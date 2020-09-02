# #Initializations/Packages
# install.packages("RMySQL")
# library(RMySQL)
# install.packages("pracma")
# library("pracma")
# install.packages('lpSolve')
# library(lpSolve)
# install.packages('purrr')
# install.packages("fitdistrplus")
# library(fitdistrplus)
#install.packages("e1071")
#install.packages("tree")
#install.packages("rpart")
#install.packages("ROCR")

#Initializations/Packages
require("RMySQL")
require("pracma")
require("lpSolve")
require("purrr")
require("fitdistrplus")
require("e1071")
require("tree")
require("caret")
require("rpart")
require("ROCR")
#------------------------------------------------------------------------------------------------------------------------------#
#Part 1) Getting basic initial solution 

#Setting Working Directory to source file
setwd(dirname(rstudioapi::getActiveDocumentContext()$path))

#PHP Account Setup
mydb <- dbConnect(MySQL(), user='g1114005', password='Cornfields2021', dbname='g1114005', host='mydb.itap.purdue.edu')
  on.exit(dbDisconnect(mydb))

#Getting InvestID
InvestID = dbGetQuery(mydb,"SELECT InvestID FROM Edges ORDER BY InvestID DESC LIMIT 1")
  

#Importing Data
#traffic_data = read.table("dot_traffic_2015.txt", header = TRUE, sep = ",")
#station_data = read.table("dot_traffic_stations_2015.txt", header=TRUE ,sep = "," )
  
traffic_data = read.table(gzfile("dot_traffic_2015.txt.gz"), header= TRUE, sep = "," ,fill = TRUE)
colnames(traffic_data)[1] = "date";
station_data = read.table(gzfile("dot_traffic_stations_2015.txt.gz"), header=TRUE ,sep = "," , fill = TRUE)
  

#Query to get input of FIPS 
region = dbGetQuery(mydb,paste0("SELECT Geographic_Region FROM Edges WHERE InvestID = '",InvestID,"'"))
region = region[1,]

midwest = c(18,17,26,39,55,19,31,20,38,27,46,29)
west = c(04,30,08,49,16,32,35,56,02,06,15,41,53)
northwest = c(09,23,25,33,44,50,34,36,42)
south = c(10,11,12,13,24,37,45,51,54,01,21,28,47,05,22,40,48)

if(region == "Midwest"){
  fips = midwest
}else if (region == "West"){
  fips = west
}else if (region == "Northwest"){
  fips = northwest
}else if (region == "South"){
  fips = south
}

#Construct column names for traffic related columns
hr.sq1 = paste0("traffic_volume_counted_after_0",0:8,"00_to_0",1:9,"00")
hr.sq2 = "traffic_volume_counted_after_0900_to_1000"
hr.sq3 = paste0("traffic_volume_counted_after_",10:23,"00_to_",11:24,"00")
hr.sqs = c(hr.sq1,hr.sq2,hr.sq3)

#selected columns for trafficDF
traffic_vars = c("date","fips_state_code", "direction_of_travel", "month_of_data", "station_id", hr.sqs)

#selected columns for stationDF
station_vars = c("fips_state_code","direction_of_travel", "latitude", "longitude", "fips_county_code","station_id")

#Creating traffic DF and station DF from given column names traffic_vars and station_vars where fips is from the selected state given by user
trafficDF=traffic_data[traffic_data$fips_state_code==fips,traffic_vars]
stationDF=station_data[station_data$fips_state_code==fips,station_vars]

#Remove duplicated rows of trafficDF
v.inds1=1:nrow(trafficDF)
names.traffic = names(trafficDF)
is.duplicated1 = duplicated(trafficDF[,!names.traffic %in% hr.sqs])
dup.inds1=v.inds1[is.duplicated1]
if(length(dup.inds1)>0){
  trafficDF=trafficDF[-dup.inds1,]}

#Remove dulicated rows of stationDF
v.inds2=1:nrow(stationDF)
is.duplicated2 = duplicated(stationDF)
dup.inds2=v.inds2[is.duplicated2]
if(length(dup.inds2)>0){
  trafficDF=trafficDF[-dup.inds2,]}

#Writing tables to store the found traffic data
dbWriteTable(mydb, "trafficDF", trafficDF, overwrite = TRUE)
dbWriteTable(mydb, "stationDF", stationDF, overwrite = TRUE)

#Creating a join function to relate both of the station and traffic data
join.df = dbGetQuery(mydb,"SELECT distinct A.*, B.latitude, B.longitude
from stationDF as B inner join trafficDF as A on A.station_id = B.station_id
and A.direction_of_travel = B.direction_of_travel;")

#Computing means for 24 hourly traffic volumes
traffic.cols.inds = grep("traffic_volume", names(join.df))
mean.by.id = aggregate(join.df[,traffic.cols.inds],
                       list(join.df$station_id),mean)
names(mean.by.id)=c("id",1:24)

#Importing the longitude and latiude edges

#Getting longitudes and latitudes of edges from database from user input.
sql = paste0("SELECT Start_long FROM Edges WHERE InvestID = '",InvestID,"'")
starting_long = dbGetQuery(mydb,sql)
sql = paste0("SELECT Start_lat FROM Edges WHERE InvestID = '",InvestID,"'")
starting_lat = dbGetQuery(mydb,sql)
sql = paste0("SELECT End_long FROM Edges WHERE InvestID = '",InvestID,"'")
ending_long = dbGetQuery(mydb,sql)
sql = paste0("SELECT End_lat FROM Edges WHERE InvestID =  '",InvestID,"'")
ending_lat = dbGetQuery(mydb,sql)
input_cords = data.frame(starting_long,starting_lat,ending_long,ending_lat) #Merge into data.frame

#Getting all the traffic volumes for the corresponding state code by looking for points between the longitudes and latitudes
solution = subset(join.df,join.df$longitude <= -min(input_cords[,c(1,3)]) & join.df$longitude >= -max(input_cords[,c(1,3)]))
solution = subset(solution, solution$latitude >= min(input_cords[,c(2,4)]) & solution$latitude <= max(input_cords[,c(2,4)]))


#add up hourly time intervals
Mean_data = rowMeans(solution[,7:30])
solution = data.frame(solution,Mean_data)
solution <- subset(solution, select = - c(7:30)) 

#Aggregating solution to get daily average per station
solution <- aggregate(cbind(solution$latitude, solution$longitude, solution$Mean_data), by = list(solution$station_id), mean)
colnames(solution) <- c("station_id", "latitude", "longitude" , "Mean_data")
solution$rowID <- 1:length(solution$station_id)


#------------------------------------------------------------------------------------------------------------------------------#
#Part 2) Using SA to find optimal charger locations


#Functions to calculate the best charger locations 

#Creating the initial solution
create_initial = function(data, budget, crit){
  cost = 0
  initial_solution = NULL
  
  #Splitting the data into two groups, one of possible hotels and other of possible convience stores
  possible_stores = data[which(data$Mean_data < crit),]
  possible_hotels = data[which(data$Mean_data >= crit),]
  
  # #Finding the list of possible places
  # while(cost <= budget){ 
  #   index = sample(dim(data)[1],1)
  #   ifelse(data[index,4] >= crit, cost <- cost + 5000, cost <- cost + 50000)
  #   initial_solution = rbind(initial_solution,data[index,])
  #   data = data[-index,]
  # }
  while(cost <= budget){
    hotel_location = possible_hotels[which.max(possible_hotels$Mean_data),]
    possible_hotels = possible_hotels[-which.max(possible_hotels$Mean_data),]
    cost = cost + 5000
    initial_solution = rbind(initial_solution,hotel_location)
     
    #Determining the number of hotels
    if(cost <= budget){
    #Determining the number of stores
    store_location = possible_stores[which.max(possible_stores$Mean_data),]
    possible_stores = possible_stores[-which.max(possible_stores$Mean_data),]
    cost = cost + 50000
    initial_solution = rbind(initial_solution,store_location)
   }
    }
  return(initial_solution) #Returns list of coordinates of possible locations
}

#Caclulating Objective value
calculate_objVal = function(sol, crit){

  #Information Given:
  revshareH = 0.2 #20% revenue share with hotel
  revshareC = 0.1 #10% revenue share with store
  DC_cost = 50000 #Installation Cost
  Type_2_cost = 5000 #Installation Cost
  rate_type_2 = 6 #Average of 6 dollar charging for each type 2 session
  rate_DC = 7.5 #Rate for session DC
  
  #Number of Stations:
  num_stores = length(which(sol$Mean_data < crit)) #num_stores is the number of stores
  num_hotels = length(which(sol$Mean_data >= crit))  #num_ is the number of hotels
  
  #Hotel Information:
  demand_hotel = sol[which(sol$Mean_data >= crit),] #Demand Hotel
  demand_hotel = demand_hotel$Mean_data #Demand Hotel
  rooms = round(demand_hotel/2) #Average number of rooms for each hotel
  op_cost_hotels = 10000 * rooms #Operation Cost for hotels
  Occupancy = 0.8 #Occupancy rate
  Revenue_per_room = 60 #Revenue per room
  
  #Store Information:
  demand_store = sol[which(sol$Mean_data < crit),]
  demand_store = demand_store$Mean_data
  Op_cost_store = 12000 * 12
  Revenue_per_customer = 10
  avg_occup = 0.8
  
  #Number of chargers for each type:
  num_type_2 = round(sum(rooms/5))
  num_dc = num_stores * 5 #Number of DC chargers
  
  #Calculation:
  
  #Hotel Profits
  hotel_profit = (Revenue_per_room * rooms * Occupancy * 365 - op_cost_hotels)#profit of hotel
  
  #Store Profit
  store_profit = (Revenue_per_customer * demand_store * avg_occup * 365 - Op_cost_store) #profit of stores
  
  #Penalty
  ifelse(hotel_profit > 0, hotel_profit <- hotel_profit, hotel_profit <- -15000000)
  ifelse(store_profit > 0, store_profit <- store_profit, store_profit <- -15000000)
  
  #Calculate Co. Profit
  company_revenue = revshareH * sum(hotel_profit + op_cost_hotels) + revshareC * sum(store_profit + Op_cost_store) + rate_DC * sum(demand_store) * avg_occup + rate_type_2 * sum(demand_hotel) * Occupancy
  company_cost = num_dc * DC_cost + num_type_2 * Type_2_cost
  company_profit = sum(company_revenue - company_cost)
  
  return(company_profit)
}

#neighbor function
neighbor = function(current_solution,data){
  remove = sample(dim(current_solution)[1],1)
  #remove = which.min(current_solution$Mean_data)
  insert = sample(dim(data)[1], 1)
  while(any(replicate(dim(current_solution)[1],data[insert,5]) == current_solution$rowID)){
      #insert = which.max(data$Mean_data)
      #data = data[-insert,]
     insert = sample(dim(data)[1], 1)
  }
  current_solution[remove,] = data[insert,]

  
  
  # remove = sample.int(current_solution$rowID, 1) #pick a random location
  # data = subset(data, data$rowID != current_solution$rowID)
  # insert = sample.int(data$rowID, 1) #pick another random location in entire dataset
  # 
  # current_solution[remove,] = data[insert,]
  # indices <- duplicated(current_solution)
  # current_solution[-indices,]

  return(current_solution)
}

#SA Function 
mySAfun = function(c,n,crit,temprature = 3000, maxit = 1000, cooling = 0.95){ 
  #c = list of possible coordinates and mean traffic data
  #n = budget
  #temprature: Initial temprature
  #maxit: Maximum number of iterations to execute for
  #cooling: rate of cooling
  
  #Generating initial Solutions
  initial_solution = create_initial(c,n,crit) #Genereate initial solution
  objective_value = calculate_objVal(initial_solution, crit) #Find objective value of initial solution
  best = initial_solution #Store initial solution 
  best_objective_value = objective_value #Store best objective value
  
  #Keeping track of best objective values throughout algorithim
  objective_value_track = best_objective_value
  cnt = 0
  
  #Running the Simulated Annealing
  for(i in 1:maxit){
    neighbor_sol = neighbor(initial_solution,c) #Generate a neighbor solution
    neighbor_obj = calculate_objVal(neighbor_sol, crit) #Calculate the objective value of the new solution (neighbor solution)
    if (neighbor_obj <= best_objective_value){ #Keep neighboring solution if it is the new best global solution
      initial_solution = neighbor_sol
      objective_value = neighbor_obj
      best = neighbor_sol
      best_objective_value = neighbor_obj
    } else if (runif(1) <= exp((neighbor_obj-objective_value)/temprature)){ #otherwise accept as best solution ##############################################
      initial_solution = neighbor_sol
      objective_value = neighbor_obj
      cnt = cnt + 1
    }
    temprature = temprature * cooling #Reduce the temprature (Update cooling)
    objective_value_track = c(objective_value_track,best_objective_value) #list the best findings 
  }
  return (list(best=best, values = objective_value_track)) #lists of best objective values
}


#Initialization to run SA function
c = solution #The list of possible coordinates
crit = median(solution$Mean_data)
n = dbGetQuery(mydb,paste0("SELECT Budget FROM Edges WHERE InvestID= '",InvestID,"'")) #The Budget of the customer
  
#Generating initial solutions
initial.sequence = create_initial(c,n, crit)
result.initial = calculate_objVal(initial.sequence, crit)

#Running SA function
trials = 30
SA.results = replicate(trials,mySAfun(c,n,crit)) #Running SA function #trials times

#Splitting results in two lists, one is the solution and the other is the objective value.
list = split(SA.results,1:2)
results = Reduce(cbind,list[[2]])

#Obtaining the result for each trial run
trial_best_objs = tail(results,1)

#Finding the trial with best objective value
best_trial = which.max(trial_best_objs)
other_objs = results[,which(1:trials != best_trial)] #Placing the other results that are not the best objective value for plotting purposes

#Finding the best and worst results
overall_best = round(max(trial_best_objs),digits = 2)
#worst
min_obj = round(min(trial_best_objs),digits = 2)
mean_obj = round(mean(trial_best_objs),digits = 2)
median_obj = round(median(trial_best_objs),digits = 2)
std_obj = round(sd(trial_best_objs),digits = 2)
#interquartile range
iqr_obj = round(IQR(trial_best_objs),digits = 2) 

#Best Solution and profit 
list_locations = list[[1]]

BEST_PROFIT = overall_best
BEST_SOLUTION = list_locations[[best_trial]]

#if any duplicates do arise in the solution - only for extreme prevention 
index1 <- which(duplicated(BEST_SOLUTION))
ifelse(is_empty(index1), BEST_SOLUTION <- BEST_SOLUTION, BEST_SOLUTION <- BEST_SOLUTION[-index1,]);

#updating DB with charger locations
BEST_SOLUTION$Charger_Type <- ifelse(BEST_SOLUTION$Mean_data >= crit, "Type2","DC")
BEST_SOLUTION$Partnership_Type <- ifelse(BEST_SOLUTION$Mean_data >= crit, "Hotel","Store")
for(r in 1:nrow(BEST_SOLUTION)){
  sql = paste0("INSERT INTO Locations (InvestID,Longitude,Latitude,StationID,ChargerType,PartnershipType,TrafficVol) VALUES(",InvestID,",",BEST_SOLUTION[r,3],", ", BEST_SOLUTION[r,2],", '", BEST_SOLUTION[r,1],"','",BEST_SOLUTION[r,6],"','",BEST_SOLUTION[r,7],"',",BEST_SOLUTION[r,4],")")
  dbGetQuery(mydb,sql)
}

#Hotels or stores classification
BEST_SOLUTION$h.label <- ifelse(BEST_SOLUTION$Mean_data >= crit, 1, 0)
BEST_SOLUTION$h.label <- as.factor(BEST_SOLUTION$h.label)

#-----------------------------------------------------------------------------------------------------------------------
#Part 3) Simulating data for the 5 year span and Classification (ML)

#Y1
data15 <- subset(BEST_SOLUTION, select = -(rowID))
data15$station_id <- as.factor(data15$station_id)
#distinguishing the best distribution
descdist(data15$Mean_data, discrete = F) 

#fitting the data with the distribution 
fitG <- fitdist(data15$Mean_data, "gamma", lower = c(0,0)) #fitting the data with the chosen distribution, beta was a close fit too but not suitable  
fitGparams = fitdistr(data15$Mean_data, densfun="gamma", lower = 0)
plot(fitG) #to graphhically check the fit 


#Simulating Demand using Sampling 

#Y2
data16 <- subset(data15, select = c(1:3,5:7))
data16$Mean_data <- rgamma(length(data15$Mean_data), fitGparams$estimate[1] , fitGparams$estimate[2]) 
data16$h.label <- ifelse(data16$Mean_data >= crit, 1, 0)
data16$h.label <- as.factor(data16$h.label)

#Y3
data17 <- subset(data15, select = c(1:3,5:7))
data17$Mean_data <- rgamma(length(data15$Mean_data), fitGparams$estimate[1] , fitGparams$estimate[2])
data17$h.label <- ifelse(data17$Mean_data >= crit, 1, 0)
data17$h.label <- as.factor(data17$h.label)

#Y4
data18 <- subset(data15, select = c(1:3,5:7))
data18$Mean_data <- rgamma(length(data15$Mean_data), fitGparams$estimate[1] , fitGparams$estimate[2])
data18$h.label <- ifelse(data18$Mean_data >= crit, 1, 0)
data18$h.label <- as.factor(data18$h.label)

#Y5
data19 <- subset(data15, select = c(1:3,5:7))
data19$Mean_data <- rgamma(length(data15$Mean_data), fitGparams$estimate[1] , fitGparams$estimate[2])
data19$h.label <- ifelse(data19$Mean_data >= crit, 1, 0)
data19$h.label <- as.factor(data19$h.label)

#Calculating profits
calculate_profits = function(sol, crit){
  
  #Information Given:
  revshareH = 0.2 #20% revenue share with hotel
  revshareC = 0.1 #10% revenue share with store
  DC_cost = 50000 #Installation Cost
  Type_2_cost = 5000 #Installation Cost
  rate_type_2 = 6 #Average of 6 dollar charging for each type 2 session
  rate_DC = 7.5 #Rate for session DC
  
  #Number of Stations:
  num_stores = length(which(sol$Mean_data < crit)) #num_stores is the number of stores
  num_hotels = length(which(sol$Mean_data >= crit))  #num_ is the number of hotels
  
  #Hotel Information:
  demand_hotel = sol[which(sol$Mean_data >= crit),] #Demand Hotel
  demand_hotel = demand_hotel$Mean_data #Demand Hotel
  rooms = round(demand_hotel/2) #Average number of rooms for each hotel
  op_cost_hotels = 10000 * rooms #Operation Cost for hotels
  Occupancy = 0.66 #Occupancy rate
  Revenue_per_room = 60 #Revenue per room
  
  #Store Information:
  demand_store = sol[which(sol$Mean_data < crit),]
  demand_store = demand_store$Mean_data
  Op_cost_store = 12000 * 12
  Revenue_per_customer = 10
  avg_occup = 0.66
  
  #Number of chargers for each type:
  num_type_2 = round(sum(rooms/5))
  num_dc = num_stores * 5 #Number of DC chargers
  
  #Calculation:
  
  #Hotel Profits
  hotel_profit = sum(Revenue_per_room * rooms * Occupancy * 365 - op_cost_hotels) #profit of hotel
  
  #Store Profit
  store_profit = sum(Revenue_per_customer * demand_store * avg_occup * 365 - Op_cost_store) #profit of stores
  
  #Calculate Co. Profit
  company_revenue = revshareH *sum(Revenue_per_room * rooms * Occupancy * 365) + revshareC * sum(Revenue_per_customer * 365 *demand_store * avg_occup) + rate_DC * sum(demand_store) * avg_occup + rate_type_2 * sum(demand_hotel) * Occupancy
  company_profit = sum(company_revenue)
  
  return(list(company_profit = company_profit, hotel_profit = hotel_profit, store_profit = store_profit))
}

#Calculating Profit for company and each type of partner
#Y1
cP1 <- BEST_PROFIT
hP1 <- as.numeric(calculate_profits(data15, crit) [2])
csP1 <- as.numeric(calculate_profits(data15, crit) [3])

#Y2
cP2 <- as.numeric(calculate_profits(data16, crit) [1])
hP2 <- as.numeric(calculate_profits(data16, crit) [2])
csP2 <- as.numeric(calculate_profits(data16, crit) [3])

#Y3
cP3 <- as.numeric(calculate_profits(data17, crit) [1])
hP3 <- as.numeric(calculate_profits(data17, crit) [2])
csP3 <- as.numeric(calculate_profits(data17, crit) [3])

#Y4
cP4 <- as.numeric(calculate_profits(data18, crit) [1])
hP4 <- as.numeric(calculate_profits(data18, crit) [2])
csP4 <- as.numeric(calculate_profits(data18, crit) [3])

#Y5
cP5 <- as.numeric(calculate_profits(data19, crit) [1])
hP5 <- as.numeric(calculate_profits(data19, crit) [2])
csP5 <- as.numeric(calculate_profits(data19, crit) [3])

CompanyProf5Y<- as.data.frame(cbind(Year = c(1:5), Profit = c(rbind(cP1, cP2, cP3, cP4, cP5))))
HotelProf5Y<- as.data.frame(cbind(Year = c(1:5), Profit = c(rbind(hP1, hP2, hP3, hP4, hP5))))
ConvStoreProf5Y<- as.data.frame(cbind(Year = c(1:5), Profit = c(rbind(csP1, csP2, csP3, csP4, csP5))))

Profits <- cbind(c(1:5), CompanyProf5Y[2], HotelProf5Y[2], ConvStoreProf5Y[2])
colnames(Profits) <- c("Year", "Company", "Hotel", "Store")

#Classification
#Training models
NB <- naiveBayes(data15, data15$h.label)
Tree <- rpart(h.label ~.,data15)

#Prediction using models and checking for accuracy
predictNB <- predict(NB, data16, type = "class")
CMnb <- confusionMatrix(predictNB, data16$h.label)
predictTree <- predict(Tree, data16, type = "class")
CMt <- confusionMatrix(predictTree, data16$h.label)

#Plotting ROC to see which classifer is better
preds <- list(as.numeric(predictNB), as.numeric(predictTree))
actuals <- rep(list(data16$h.label), 2)
pred1 <- prediction(preds, actuals)
perf1 <- performance(pred1, "tpr", "fpr")
plot(perf1, col = as.list(c(4,2)), main = "ROC Curves for both classifiers")
legend (x = "bottomright", legend = c("Naive Bayes", "Tree"),fill = c(4,2))
abline(a = 0, b = 1, lty = 2)


#Using chosen trained model to predict the classification 
predictNB1 <- predict(NB, data17, type = "class")
CMnb1 <- confusionMatrix(predictNB1, data17$h.label)
predictNB2 <- predict(NB, data18, type = "class")
CMnb2 <- confusionMatrix(predictNB2, data18$h.label)
predictNB3 <- predict(NB, data19, type = "class")
CMnb3 <- confusionMatrix(predictNB3, data19$h.label)

##Accuracy at par with the trained model's accuracy, thus an indication of the right distibution 


#Part 4) Plots to be generated for Client

prPlot <- ggplot(Profits, aes(x = Year)) + geom_line(aes(y = Company, color = "Company"), stat = "identity") + geom_line(aes(y = Hotel, color = "Hotel"), stat = "identity" ) + geom_line(aes(y = Store, color = "Store"), stat = "identity") + ggtitle("Profits for the 5 year span") + theme(plot.title = element_text(hjust = 0.5)) 
prPlot = print(prPlot + labs(y = "Profits"))

traffic_frame = as.data.frame(cbind(1:dim(data15)[1],data15$Mean_data,data16$Mean_data,data17$Mean_data,data18$Mean_data,data19$Mean_data))
colnames(traffic_frame) = c("Index","Year1","Year2","Year3","Year4","Year5")
Dplot <- ggplot(traffic_frame, aes(x=Index)) + geom_point(stat = "identity",aes(y=Year1,color = "Year1")) + geom_point(stat = "identity",aes(y=Year2,color = "Year2"))+geom_point(stat = "identity",aes(y=Year3,color = "Year3"))+geom_point(stat = "identity",aes(y=Year4,color = "Year4"))+geom_point(stat = "identity",aes(y=Year5,color = "Year5")) + ggtitle("Average Daily Traffic For Five Year Span") + theme(plot.title = element_text(hjust = 0.5)) 
Dplot = print(Dplot + labs(y="Traffic Demand")) 
  
pdf("ClientReport_Out", height = 11, width = 8.5, onefile = T, title = "Customized Graphic Output", paper= "letter") 
prPlot
Dplot
dev.off()


















