#Initialization of Packages to be installed
#install.packages("igraph")
#library(igraph)
#install.packages("RMySQL")
#library(RMySQL)
require(ggmap)

#PHP Account Setup
require(RMySQL)
mydb <- dbConnect(MySQL(), user='g1114005', password='Cornfields2021', dbname='g1114005', host='mydb.itap.purdue.edu')
on.exit(dbDisconnect(mydb))

#Inputs from website from user

origAddress = dbGetQuery(mydb,paste0("SELECT Address FROM Address ORDER BY PersonID DESC LIMIT 1"))
origAddress = origAddress[1,]
option = dbGetQuery(mydb,paste0("SELECT Preference FROM Address ORDER BY PersonID DESC LIMIT 1"))
option = option[1,]
#Registration Key
register_google(key="AIzaSyAdnZmsL5wOqec1dhIHpDD9Q6K-1bH1zKQ")

#load the library ggmap
#library(ggmap)

#Initialize the data frame
geocoded = data.frame(stringsAsFactors = FALSE)
result = geocode(origAddress, output = "latlona", source = "google")

#Write a CSV file containing the computed longitudes and latitudes of the input values
given_longitude = result$lon 
given_latitude = result$lat

#Getting all charger locations
edges  = dbGetQuery(mydb,paste0("SELECT * FROM Locations WHERE PartnershipType = '",option,"'"))

#Calculating the distance between two coordinates
longitude2 = edges$Longitude
longitude1 = c(replicate(length(longitude2),given_longitude))
latitude2 = edges$Latitude
latitude1 = c(replicate(length(latitude2), given_latitude))
distance = sqrt((longitude2 - longitude1)^2 + (latitude2 - latitude1))
ids = c(2:length(longitude2))
id_start = replicate(length(ids),1)

#Closest Charger
index_close = which.min(distance)
closest_charger = edges[index_close,]
charger_long = closest_charger$Longitude
charger_lat = closest_charger$Latitude


frame = as.data.frame(cbind(charger_long, charger_lat,given_longitude,given_latitude))

#Creating temp table
dbWriteTable(mydb, "Route", frame, overwrite = TRUE)
#print(charger_long)
#print(charger_lat)
#print(given_longitude)
#print(given_latitude)


