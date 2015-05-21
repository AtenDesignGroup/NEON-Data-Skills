---
layout: post
title: "Working With Time Series Data Within a Nested HDF5 File in R"
date:   2015-1-28 20:49:52
dateCreated:   2014-11-17 20:49:52
lastModified:   2015-05-21 20:49:52
estimatedTime: 1.0 - 1.5 Hours
packagesLibraries: rhdf5, ggplot2, dplyr
authors: Leah A Wasser, Ted Hart
contributors: Elizabeth Webb
categories: [coding-and-informatics]
category: coding-and-informatics
tags: [HDF5,R]
mainTag: HDF5
description: "Explore, extract and visualize temporal temperature data collected from a NEON flux tower from multiple sites and sensors in R. Learn how to extract metadata and how to use nested loops and dplyr to perform more advanced queries and data manipulation."
code1: explore_dataViz.R
image:
  feature: hierarchy_folder_purple.png
  credit: Colin Williams NEON, Inc.
  creditlink: http://www.neoninc.org
permalink: /HDF5/Explore-HDF5-Using-R/
comments: true
---


{% highlight r %}
#source("http://bioconductor.org/biocLite.R")
#biocLite("rhdf5")


library("rhdf5")
library("ggplot2")
library("dplyr")
{% endhighlight %}

Let's begin by opening up a H5 file


{% highlight r %}
f <- 'NEON_TowerDataD3_D10.hdf5'
h5ls(f)
{% endhighlight %}



{% highlight text %}
##                               group        name       otype   dclass  dim
## 0                                 /   Domain_03   H5I_GROUP              
## 1                        /Domain_03        OSBS   H5I_GROUP              
## 2                   /Domain_03/OSBS       min_1   H5I_GROUP              
## 3             /Domain_03/OSBS/min_1      boom_1   H5I_GROUP              
## 4      /Domain_03/OSBS/min_1/boom_1 temperature H5I_DATASET COMPOUND 4323
## 5             /Domain_03/OSBS/min_1      boom_2   H5I_GROUP              
## 6      /Domain_03/OSBS/min_1/boom_2 temperature H5I_DATASET COMPOUND 4323
## 7             /Domain_03/OSBS/min_1      boom_3   H5I_GROUP              
## 8      /Domain_03/OSBS/min_1/boom_3 temperature H5I_DATASET COMPOUND 4323
## 9             /Domain_03/OSBS/min_1      boom_5   H5I_GROUP              
## 10     /Domain_03/OSBS/min_1/boom_5 temperature H5I_DATASET COMPOUND 4323
## 11            /Domain_03/OSBS/min_1   tower_top   H5I_GROUP              
## 12  /Domain_03/OSBS/min_1/tower_top temperature H5I_DATASET COMPOUND 4323
## 13                  /Domain_03/OSBS      min_30   H5I_GROUP              
## 14           /Domain_03/OSBS/min_30      boom_1   H5I_GROUP              
## 15    /Domain_03/OSBS/min_30/boom_1 temperature H5I_DATASET COMPOUND  147
## 16           /Domain_03/OSBS/min_30      boom_2   H5I_GROUP              
## 17    /Domain_03/OSBS/min_30/boom_2 temperature H5I_DATASET COMPOUND  147
## 18           /Domain_03/OSBS/min_30      boom_3   H5I_GROUP              
## 19    /Domain_03/OSBS/min_30/boom_3 temperature H5I_DATASET COMPOUND  147
## 20           /Domain_03/OSBS/min_30      boom_5   H5I_GROUP              
## 21    /Domain_03/OSBS/min_30/boom_5 temperature H5I_DATASET COMPOUND  147
## 22           /Domain_03/OSBS/min_30   tower_top   H5I_GROUP              
## 23 /Domain_03/OSBS/min_30/tower_top temperature H5I_DATASET COMPOUND  147
## 24                                /   Domain_10   H5I_GROUP              
## 25                       /Domain_10        STER   H5I_GROUP              
## 26                  /Domain_10/STER       min_1   H5I_GROUP              
## 27            /Domain_10/STER/min_1      boom_1   H5I_GROUP              
## 28     /Domain_10/STER/min_1/boom_1 temperature H5I_DATASET COMPOUND 4323
## 29            /Domain_10/STER/min_1      boom_2   H5I_GROUP              
## 30     /Domain_10/STER/min_1/boom_2 temperature H5I_DATASET COMPOUND 4323
## 31            /Domain_10/STER/min_1      boom_3   H5I_GROUP              
## 32     /Domain_10/STER/min_1/boom_3 temperature H5I_DATASET COMPOUND 4323
## 33                  /Domain_10/STER      min_30   H5I_GROUP              
## 34           /Domain_10/STER/min_30      boom_1   H5I_GROUP              
## 35    /Domain_10/STER/min_30/boom_1 temperature H5I_DATASET COMPOUND  147
## 36           /Domain_10/STER/min_30      boom_2   H5I_GROUP              
## 37    /Domain_10/STER/min_30/boom_2 temperature H5I_DATASET COMPOUND  147
## 38           /Domain_10/STER/min_30      boom_3   H5I_GROUP              
## 39    /Domain_10/STER/min_30/boom_3 temperature H5I_DATASET COMPOUND  147
{% endhighlight %}



{% highlight r %}
# HDF5 allows us to quickly extract parts of a dataset or even groups.
# extract temperature data from one site (Ordway Swisher, Florida) and plot it

temp <- h5read(f,"/Domain_03/OSBS/min_1/boom_1/temperature")
#view the header and the first 6 rows of the dataset
head(temp)
{% endhighlight %}



{% highlight text %}
##                    date numPts     mean      min      max    variance
## 1 2014-04-01 00:00:00.0     60 15.06154 14.96886 15.15625 0.002655015
## 2 2014-04-01 00:01:00.0     60 14.99858 14.93720 15.04274 0.001254117
## 3 2014-04-01 00:02:00.0     60 15.26231 15.03502 15.56683 0.041437537
## 4 2014-04-01 00:03:00.0     60 15.45351 15.38553 15.53449 0.001174759
## 5 2014-04-01 00:04:00.0     60 15.35306 15.23799 15.42346 0.003526443
## 6 2014-04-01 00:05:00.0     60 15.12807 15.05846 15.23494 0.003764170
##        stdErr uncertainty
## 1 0.006652087  0.01620325
## 2 0.004571866  0.01306111
## 3 0.026279757  0.05349682
## 4 0.004424851  0.01286833
## 5 0.007666423  0.01788372
## 6 0.007920616  0.01831239
{% endhighlight %}



{% highlight r %}
#generate a quick plot, type - l for line 
plot(temp$mean,type='l')
{% endhighlight %}

![center](/_posts/figs/testThis/unnamed-chunk-2-1.png) 

{% highlight r %}
#let's fix up the plot above a bit. We can first add dates to the x axis. 
#in order to list dates, we need to specify the format that the date field is in.
temp$date <- as.POSIXct(temp$date ,format = "%Y-%m-%d %H:%M:%S", tz = "EST")

ordwayPlot <- qplot (date,mean,data=temp,geom="line", title="ordwayData",
                 main="Mean Temperature - Ordway Swisher", xlab="Date", 
                 ylab="Mean Temperature (Degrees C)")

#let's check out the plot
ordwayPlot
{% endhighlight %}

![center](/_posts/figs/testThis/unnamed-chunk-2-2.png) 

{% highlight r %}
####################
#more info on customizing plots
#http://www.statmethods.net/advgraphs/ggplot2.html
######################
{% endhighlight %}


{% highlight r %}
## View the groups and datasets in our file, 
#we will grab the nested structure, 5 'levels' down
#5 levels gets us to the temperature dataset
fiu_struct <- h5ls(f,recursive=5)

## have a look at the structure.
fiu_struct
{% endhighlight %}



{% highlight text %}
##                               group        name       otype   dclass  dim
## 0                                 /   Domain_03   H5I_GROUP              
## 1                        /Domain_03        OSBS   H5I_GROUP              
## 2                   /Domain_03/OSBS       min_1   H5I_GROUP              
## 3             /Domain_03/OSBS/min_1      boom_1   H5I_GROUP              
## 4      /Domain_03/OSBS/min_1/boom_1 temperature H5I_DATASET COMPOUND 4323
## 5             /Domain_03/OSBS/min_1      boom_2   H5I_GROUP              
## 6      /Domain_03/OSBS/min_1/boom_2 temperature H5I_DATASET COMPOUND 4323
## 7             /Domain_03/OSBS/min_1      boom_3   H5I_GROUP              
## 8      /Domain_03/OSBS/min_1/boom_3 temperature H5I_DATASET COMPOUND 4323
## 9             /Domain_03/OSBS/min_1      boom_5   H5I_GROUP              
## 10     /Domain_03/OSBS/min_1/boom_5 temperature H5I_DATASET COMPOUND 4323
## 11            /Domain_03/OSBS/min_1   tower_top   H5I_GROUP              
## 12  /Domain_03/OSBS/min_1/tower_top temperature H5I_DATASET COMPOUND 4323
## 13                  /Domain_03/OSBS      min_30   H5I_GROUP              
## 14           /Domain_03/OSBS/min_30      boom_1   H5I_GROUP              
## 15    /Domain_03/OSBS/min_30/boom_1 temperature H5I_DATASET COMPOUND  147
## 16           /Domain_03/OSBS/min_30      boom_2   H5I_GROUP              
## 17    /Domain_03/OSBS/min_30/boom_2 temperature H5I_DATASET COMPOUND  147
## 18           /Domain_03/OSBS/min_30      boom_3   H5I_GROUP              
## 19    /Domain_03/OSBS/min_30/boom_3 temperature H5I_DATASET COMPOUND  147
## 20           /Domain_03/OSBS/min_30      boom_5   H5I_GROUP              
## 21    /Domain_03/OSBS/min_30/boom_5 temperature H5I_DATASET COMPOUND  147
## 22           /Domain_03/OSBS/min_30   tower_top   H5I_GROUP              
## 23 /Domain_03/OSBS/min_30/tower_top temperature H5I_DATASET COMPOUND  147
## 24                                /   Domain_10   H5I_GROUP              
## 25                       /Domain_10        STER   H5I_GROUP              
## 26                  /Domain_10/STER       min_1   H5I_GROUP              
## 27            /Domain_10/STER/min_1      boom_1   H5I_GROUP              
## 28     /Domain_10/STER/min_1/boom_1 temperature H5I_DATASET COMPOUND 4323
## 29            /Domain_10/STER/min_1      boom_2   H5I_GROUP              
## 30     /Domain_10/STER/min_1/boom_2 temperature H5I_DATASET COMPOUND 4323
## 31            /Domain_10/STER/min_1      boom_3   H5I_GROUP              
## 32     /Domain_10/STER/min_1/boom_3 temperature H5I_DATASET COMPOUND 4323
## 33                  /Domain_10/STER      min_30   H5I_GROUP              
## 34           /Domain_10/STER/min_30      boom_1   H5I_GROUP              
## 35    /Domain_10/STER/min_30/boom_1 temperature H5I_DATASET COMPOUND  147
## 36           /Domain_10/STER/min_30      boom_2   H5I_GROUP              
## 37    /Domain_10/STER/min_30/boom_2 temperature H5I_DATASET COMPOUND  147
## 38           /Domain_10/STER/min_30      boom_3   H5I_GROUP              
## 39    /Domain_10/STER/min_30/boom_3 temperature H5I_DATASET COMPOUND  147
{% endhighlight %}



{% highlight r %}
#now we can use this object to pull group paths from our file!
fiu_struct[3,1]
{% endhighlight %}



{% highlight text %}
## [1] "/Domain_03/OSBS"
{% endhighlight %}



{% highlight r %}
## Let's view the metadata for the OSBS group
OSBS  <- h5readAttributes(f,fiu_struct[3,1])
#view the attributes
OSBS
{% endhighlight %}



{% highlight text %}
## $LatLon
## [1] "29.68927/-81.99343"
## 
## $`Site Name`
## [1] "Ordway-Swisher Biological Station Site"
{% endhighlight %}



{% highlight r %}
#grab the lat and long from the data
#note we might want to format the lat and long differently 
#this format is more difficult to extract from R!
OSBS$LatLon
{% endhighlight %}



{% highlight text %}
## [1] "29.68927/-81.99343"
{% endhighlight %}

#Challenge

1. How would you rewrite the metadata for each site to make it more user friendly? Discuss with your neighbor. Map out an H5 file that might contain more useful information.



{% highlight r %}
#r compare temperature data for different booms at the Ordway Swisher site.
library(dplyr)
library(ggplot2)


#use dplyr to subset data by dataset name (temperature)
# and site / 1 minute average
newStruct <- fiu_struct %>% filter(grepl("temperature",name),
                                   grepl("OSBS/min_1",group))

#create final paths
paths <- paste(newStruct$group,newStruct$name,sep="/")

#create a new, empty data.frame
ord_temp <- data.frame()

#loop through each temp dataset and add to data.frame
for(i in paths){
  datasetName <- i
  print(datasetName) 
  #read in each dataset in the H5 list
  dat <- h5read(f,datasetName)
  # add boom name to data.frame
  print(strsplit(i,"/")[[1]][5]) 
  dat$boom <- strsplit(i,"/")[[1]][5]
  ord_temp <- rbind(ord_temp,dat)
}
{% endhighlight %}



{% highlight text %}
## [1] "/Domain_03/OSBS/min_1/boom_1/temperature"
## [1] "boom_1"
## [1] "/Domain_03/OSBS/min_1/boom_2/temperature"
## [1] "boom_2"
## [1] "/Domain_03/OSBS/min_1/boom_3/temperature"
## [1] "boom_3"
## [1] "/Domain_03/OSBS/min_1/boom_5/temperature"
## [1] "boom_5"
## [1] "/Domain_03/OSBS/min_1/tower_top/temperature"
## [1] "tower_top"
{% endhighlight %}



{% highlight r %}
#fix the dates
ord_temp$date <- as.POSIXct(ord_temp$date,format = "%Y-%m-%d %H:%M:%S", tz = "EST")

#plot the data
ggplot(ord_temp,aes(x=date,y=mean,group=boom,colour=boom))+
  geom_path()+
  ylab("Mean temperature") + xlab("Date")+
  theme_bw()+
  ggtitle("3 Days of temperature data at Ordway Swisher")+
  scale_x_datetime( breaks=pretty_breaks(n=4))
{% endhighlight %}



{% highlight text %}
## Error in scale_datetime(c("x", "xmin", "xmax", "xend"), expand = expand, : could not find function "pretty_breaks"
{% endhighlight %}

Create a summary plot comparing temperature at two sites.


{% highlight r %}
#grab just the paths to temperature data, 30 minute average
pathStrux <- fiu_struct %>% filter(grepl("temperature",name), 
                                   grepl("min_30",group)) 
#create final paths
paths <- paste(pathStrux$group,pathStrux$name,sep="/")

#create empty dataframe
temp_30 <- data.frame()

for(i in paths){
  #create columns for boom name and site name
  boom <-  strsplit(i,"/")[[1]][5]
  site <- strsplit(i,"/")[[1]][3]
  dat <- h5read(f,i)
  dat$boom <- boom
  dat$site <- site
 temp_30 <- rbind(temp_30,dat)
}

#Assign the date field to a "date" format in R
temp_30$date <- as.POSIXct(temp_30$date,format = "%Y-%m-%d %H:%M:%S")

# generate a mean temperature for every date across booms
temp30_sum <- temp_30 %>% group_by(date,site) %>% summarise(mean = mean(mean))

#Create plot!
ggplot(temp30_sum,aes(x=date,y=mean,group=site,colour=site)) + 
  geom_path()+ylab("Mean temperature, 30 Minute Average") + 
  xlab("Date")+
  theme_bw()+
  ggtitle("Comparison of Ordway-Swisher Biological Station (FL) vs North Sterling (CO)") +
  scale_x_datetime( breaks=pretty_breaks(n=4))
{% endhighlight %}



{% highlight text %}
## Error in scale_datetime(c("x", "xmin", "xmax", "xend"), expand = expand, : could not find function "pretty_breaks"
{% endhighlight %}
