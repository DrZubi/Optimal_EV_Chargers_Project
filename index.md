# Welcome to Optimal_EV_Chargers_Project Respitory!
Created by Prarthna Khemka, Shaurya Malik, and Omar Zu'bi.

### Project Description
16-week long group project to help companys find optimal charging locations based on US traffic volumes, with the end goal of maximizing revenue.

* Methodology: Simulating Annealing, Greedy Algorithms, Google Geo-Code API, Google Maps-Places API, Google Routing API, MySQL, R, HTML, CSS, PHP, JS
* Result: A web interface that users can log into and plan out optimal locations to place EV chargers. The web interface also included a live routing GPS for EV drivers to find the best route to the closest, available EV charger.

### Simulation Model
The group was tasked with creating an algorithm that will simulate projected traffic in all of the United
States, based on the traffic data spreadsheet provided and other sources. The goal of this is to create a
better estimation of traffic based on the traffic pattern acquired from past data. The further goal is to
rely less on the data spreadsheet and more on the projected estimation and to continually improve this
algorithm, adding more sources for better accuracy.

To simulate the data we first found which distribution fit the traffic the best, using the graph seen in
Figure 1., we noticed that beta was the best fit but beta is a distribution form 0 to 1, thus we used
gamma which was the next closest and used gamma distribution parameters found from the traffic data
given to us to generate the traffic for the next 5 years. The fit of gamma to the distribution can be seen
visually through Figure 2.

Upon entering the dedicated website portal, every client will have the option to click on a command prompt that will redirect them to the Automated Station Spot landing page. Clients will then be required to specify their city of interest, allocated budget and geographical region in the input boxes. Based on the information provided the user, Automated Station Spot will then formulate several recommended solutions. ACSTM also caters to clients that are not certain with their budget just yet, instead, clients will also be able to enter a range of budgets. Our system will generate a list of coordinates thatare potential charging station locations that will be able to maximize client’s profits across five years. These determined values are based off the number of chargers that are to be situated, the number ofhotels and/or convenience stores constructed, the predicted demand as well as the overall cost. The On Route Navigation (ORN) is the second part of the web interface created to increase qualityof user experience. By taking in the users current address and their preferences for different chargingexperiences: whether they want to charge on the go, charge while eating food, charge while stayingovernight, charge while doing a quick shopping run, whatever may be the users preferences, ORNTMroutes the user to their desired destination using the shortest path possible, thus prioritizing the user’s time.

### Optamization Model

### Database Design 

### Website Features
* Reports completed in Latex
* Seprate signup and login pages for drivers and companies
* Desktop and Mobile compatible web page
* Geocode to convert addressed to coordinates
* Recaptcha was added to sign up and login to increase security
* Simulation of demand using fitted distribution
* Video Presentation created
* Google Direction API to route
* Google Map API for routing
* Company’s vision, values, and goals established, then aligned the project to meet them•Allows the clients to input a csv file of the edges required, thus enhancing their experience•Personalized Name on Website•Created two different web interface experiences based on if it is a client or a user

### Website Interface Preview

