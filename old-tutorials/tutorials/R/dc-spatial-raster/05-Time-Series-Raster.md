---
syncID: 0f5e34fdc92349d4a3e317b83b6e38f6
title:  "Raster 05: Raster Time Series Data in R"	
description: "This tutorial covers how to work with and plot a raster time series, using an R RasterStack object. It also covers the basics of practical data quality assessment of remote sensing imagery."	
dateCreated: 2014-11-26
authors: Leah A. Wasser, Megan A. Jones, Zack Brym, Kristina Riemer, Jason Williams, Jeff Hollister,  Mike Smorul	
contributors:	
estimatedTime:	
packagesLibraries: raster, rgdal
topics: raster, spatial-data-gis
languagesTool: R
dataProduct: NEON.DP2.30026, NEON.DP3.30026
code1: /R/dc-spatial-raster/05-Time-Series-Raster.R	
tutorialSeries: raster-data-series, raster-time-series
urlTitle: dc-raster-time-series-r

---

This tutorial covers how to work with and plot a raster time series, using an 
R `RasterStack` object. It also covers practical assessment of data quality in
remote sensing derived imagery.


<div id="ds-objectives" markdown="1">

## Learning Objectives

After completing this tutorial, you will be able to:

* Understand the format of a time series raster dataset.
* Know how to work with time series rasters. 
* Be able to efficiently import a set of rasters stored in a single directory.
* Be able to plot and explore time series raster data using the `plot()`
function in R.

## Things You’ll Need To Complete This Tutorial
You will need the most current version of R and, preferably, `RStudio` loaded
on your computer to complete this tutorial.

### Install R Packages

* **raster:** `install.packages("raster")`
* **rgdal:** `install.packages("rgdal")`

* <a href="{{ site.baseurl }}/packages-in-r" target="_blank"> More on Packages in R </a>– Adapted from Software Carpentry.

#### Data to Download
{% include/dataSubsets/_data_Landsat-NDVI.html %}

****

{% include/_greyBox-wd-rscript.html %}

****

### Additional Resources
* <a href="http://cran.r-project.org/web/packages/raster/raster.pdf" target="_blank">
Read more about the `raster` package in R.</a>

</div>

## About Raster Time Series Data

A raster data file can contain one single band or many bands. If the raster data
contains imagery data, each band may represent reflectance for a different 
wavelength (color or type of light) or set of wavelengths - for
example red, green and blue. A multi-band raster may two or more bands or layers
of data collected at different times for the same **extent** (region) and of the 
same **resolution**.

<figure>
    <a href="{{ site.baseurl }}/images/dc-spatial-raster/GreennessOverTime.jpg">
    <img src="{{ site.baseurl }}/images/dc-spatial-raster/GreennessOverTime.jpg"></a>
    <figcaption>A multi-band raster dataset can contain time series data. 
    Source: National Ecological Observatory Network (NEON). 
    </figcaption>
</figure>

The raster data that we will use in this tutorial are located in the
(`NEON-DS-Landsat-NDVI\HARV\2011\NDVI`) directory and cover part of the 
<a href="/field-sites/field-sites-map/HARV" target="_blank">NEON Harvard Forest field site</a>.

In this tutorial, we will:

1. Import NDVI data in `GeoTIFF` format.
2. Import, explore and plot NDVI data derived for several dates throughout the
year. 
3. View the RGB imagery used to derived the NDVI time series to better
understand unusual / outlier values. 

## NDVI Data
The Normalized Difference Vegetation Index or NDVI is a quantitative index of 
greenness ranging from 0-1 where 0 represents minimal or no greenness and 1 
represents maximum greenness. 

NDVI is often used for a quantitative proxy measure of vegetation health, cover
and phenology (life cycle stage) over large areas. Our NDVI data are a Landsat
derived single band product saved as a GeoTIFF for different times of the year. 

<figure>
 <a href="http://earthobservatory.nasa.gov/Features/MeasuringVegetation/Images/ndvi_example.jpg"> 
 <img src="http://earthobservatory.nasa.gov/Features/MeasuringVegetation/Images/ndvi_example.jpg"></a>
    <figcaption>NDVI is calculated from the visible and near-infrared light
    reflected by vegetation. Healthy vegetation (left) absorbs most of the
    visible light that hits it, and reflects a large portion of
    near-infrared light. Unhealthy or sparse vegetation (right) reflects more
    visible light and less near-infrared light. Image & Caption Source: NASA 
    </figcaption>
</figure>

* <a href="http://earthobservatory.nasa.gov/Features/MeasuringVegetation/measuring_vegetation_2.php" target="_blank">
More on NDVI from NASA</a>

## RGB Data
While the NDVI data are a single band product, the RGB images that contain the
red band used to derive NDVI, contain 3 (of the 7) 30m resolution bands
available from Landsat data. The RGB directory contains RGB images for each time
period that NDVI is available.


<figure>
    <a href="{{ site.baseurl }}/images/dc-spatial-raster/RGBSTack_1.jpg">
    <img src="{{ site.baseurl }}/images/dc-spatial-raster/RGBSTack_1.jpg"></a>
    <figcaption>A "true" color image consists of 3 bands - red, green and blue. 
    When composited or rendered together in a GIS, or even a image-editor like
    Photoshop the bands create a color image. 
	Source: National Ecological Observatory Network (NEON).  
    </figcaption>
</figure>

### Getting Started 
In this tutorial, we will use the `raster` and `rgdal` libraries.


    # load packages
    library(raster)
    library(rgdal)
    
    # set working directory to ensure R can find the file we wish to import
    # setwd("working-dir-path-here")

To begin, we will create a list of raster files using the `list.files()` 
function in R. This list will be used to generate a `RasterStack`. We will
only add files to our list with a `.tif` extension using the syntax
`pattern=".tif$"`.

If we specify `full.names=TRUE`, the full path for each file will be added to
the list. 


    # Create list of NDVI file paths
    # assign path to object = cleaner code
    NDVI_HARV_path <- "NEON-DS-Landsat-NDVI/HARV/2011/NDVI" 
    all_NDVI_HARV <- list.files(NDVI_HARV_path,
                                full.names = TRUE,
                                pattern = ".tif$")
    
    # view list - note the full path, relative to our working directory, is included
    all_NDVI_HARV

    ##  [1] "NEON-DS-Landsat-NDVI/HARV/2011/NDVI/005_HARV_ndvi_crop.tif"
    ##  [2] "NEON-DS-Landsat-NDVI/HARV/2011/NDVI/037_HARV_ndvi_crop.tif"
    ##  [3] "NEON-DS-Landsat-NDVI/HARV/2011/NDVI/085_HARV_ndvi_crop.tif"
    ##  [4] "NEON-DS-Landsat-NDVI/HARV/2011/NDVI/133_HARV_ndvi_crop.tif"
    ##  [5] "NEON-DS-Landsat-NDVI/HARV/2011/NDVI/181_HARV_ndvi_crop.tif"
    ##  [6] "NEON-DS-Landsat-NDVI/HARV/2011/NDVI/197_HARV_ndvi_crop.tif"
    ##  [7] "NEON-DS-Landsat-NDVI/HARV/2011/NDVI/213_HARV_ndvi_crop.tif"
    ##  [8] "NEON-DS-Landsat-NDVI/HARV/2011/NDVI/229_HARV_ndvi_crop.tif"
    ##  [9] "NEON-DS-Landsat-NDVI/HARV/2011/NDVI/245_HARV_ndvi_crop.tif"
    ## [10] "NEON-DS-Landsat-NDVI/HARV/2011/NDVI/261_HARV_ndvi_crop.tif"
    ## [11] "NEON-DS-Landsat-NDVI/HARV/2011/NDVI/277_HARV_ndvi_crop.tif"
    ## [12] "NEON-DS-Landsat-NDVI/HARV/2011/NDVI/293_HARV_ndvi_crop.tif"
    ## [13] "NEON-DS-Landsat-NDVI/HARV/2011/NDVI/309_HARV_ndvi_crop.tif"

Now we have a list of all GeoTIFF files in the `NDVI` directory for Harvard
Forest. Next, we will create a `RasterStack` from this list using the `stack()` 
function. 


    # Create a raster stack of the NDVI time series
    NDVI_HARV_stack <- stack(all_NDVI_HARV)

We can explore the GeoTIFF tags (the embedded metadata) in a `stack` using the
same syntax that we used on single-band raster objects in R including: `crs()`
(coordinate reference system), `extent()` and `res()` (resolution; specifically
`yres()` and `xres()`).


    # view crs of rasters
    crs(NDVI_HARV_stack)

    ## CRS arguments:
    ##  +proj=utm +zone=19 +ellps=WGS84 +units=m +no_defs

    # view extent of rasters in stack
    extent(NDVI_HARV_stack)

    ## class       : Extent 
    ## xmin        : 239415 
    ## xmax        : 239535 
    ## ymin        : 4714215 
    ## ymax        : 4714365

    # view the y resolution of our rasters
    yres(NDVI_HARV_stack)

    ## [1] 30

    # view the x resolution of our rasters
    xres(NDVI_HARV_stack)

    ## [1] 30

Notice that the CRS is `+proj=utm +zone=19 +ellps=WGS84 +units=m +no_defs`. The
CRS is in UTM Zone 19.  If you have completed the previous tutorials in 
this 
<a href="/raster-data-series" target="_blank">raster data in R series</a>,
you may have noticed that the UTM zone for the NEON collected remote sensing 
data was in Zone 18 rather than Zone 19. Why are the Landsat data in Zone 19?  

<figure>
    <a href="{{ site.baseurl }}/images/dc-spatial-raster/UTM_zones_18-19.jpg">
    <img src="{{ site.baseurl }}/images/dc-spatial-raster/UTM_zones_18-19.jpg"></a>
    <figcaption> Landsat imagery swaths are over 170 km N-S and 180 km E-W. As 
	a result a given image may overlap two UTM zones. The designated zone is 
	determined by the zone that the majority of the image is in.  In this
	example, our point of interest is in UTM Zone 18 but the Landsat image will 
	be classified as UTM Zone 19. Source: National Ecological Observatory 
	Network (NEON).  
    </figcaption>
</figure>

The width of a Landsat scene is extremely wide - spanning over 170km north to 
south and 180km east to west. This means that Landsat data often cover multiple 
UTM zones. When the data are processed, the zone in which the majority of the
data cover, is the zone which is used for the final CRS. Thus, our field site at
Harvard Forest is located in UTM Zone 18, but the Landsat data are in a `CRS` of
UTM Zone 19.

<div id="ds-challenge" markdown="1">
### Challenge: Raster Metadata
Answer the following questions about our `RasterStack`.

1. What is the CRS?
2. What is the x and y resolution of the data? 
3. What units is the above resolution in?

</div>



## Plotting Time Series Data
Once we have created our `RasterStack`, we can visualize our data. We can use
the `plot()` command to quickly plot a `RasterStack`.


    # view a plot of all of the rasters
    # 'nc' specifies number of columns (we will have 13 plots)
    plot(NDVI_HARV_stack, 
         zlim = c(1500, 10000), 
         nc = 4)

![ ]({{ site.baseurl }}/images/rfigs/R/dc-spatial-raster/05-Time-Series-Raster/plot-time-series-1.png)

Have a look at the range of NDVI values observed in the plot above. We know that
the accepted values for NDVI range from 0-1. Why does our data range from
0 - 10,000? 

## Scale Factors
The metadata for this NDVI data specifies a scale factor: 10,000. A scale factor
is sometimes used to maintain smaller file sizes by removing decimal places. 
Storing data in integer format keeps files sizes smaller.

Let's apply the scale factor before we go any further. Conveniently, we can 
quickly apply this factor using raster math on the entire stack as follows:

`raster_stack_object_name / 10000`

<div id="ds-dataTip" markdown="1">
<i class="fa fa-star"></i> **Data Tip:** We can make this plot  
even prettier by fixing the individual tile names, adding an plot title and by
using the (`levelplot`) function. This is covered in the NEON Data Skills 
<a href="/dc-raster-rastervis-levelplot-r" target="_blank">*Plot Time Series Rasters in R*</a>
tutorial. 
</div>


    # apply scale factor to data
    NDVI_HARV_stack <- NDVI_HARV_stack/10000
    # plot stack with scale factor applied
    # apply scale factor to limits to ensure uniform plottin
    plot(NDVI_HARV_stack,
         zlim = c(.15, 1),  
         nc = 4)

![ ]({{ site.baseurl }}/images/rfigs/R/dc-spatial-raster/05-Time-Series-Raster/apply-scale-factor-1.png)

## Take a Closer Look at Our Data
Let's take a closer look at the plots of our data. Note that Massachusetts, 
where the NEON Harvard Forest Field Site is located has a fairly consistent
fall, winter, spring and summer season where vegetation turns green in the
spring, continues to grow throughout the summer, and begins to change colors and
senesce in the fall through winter. Do you notice anything that seems unusual
about the patterns of greening and browning observed in the plots above?

Hint: the number after the "X" in each tile title is the Julian day which in
this case represents the number of days into each year. If you are unfamiliar
with Julian day, check out the NEON Data Skills 
<a href="/julian-day-conversion-r" target="_blank">*Converting to Julian Day* tutorial </a>.

## View Distribution of Raster Values
In the above exercise, we viewed plots of our NDVI time series and noticed a 
few images seem to be unusually light. However this was only a visual
representation of potential issues in our data. What is another way we can look
at these data that is quantitative? 

Next we will use histograms to explore the distribution of NDVI values stored in
each raster.


    # create histograms of each raster
    hist(NDVI_HARV_stack, 
         xlim = c(0, 1))

![ ]({{ site.baseurl }}/images/rfigs/R/dc-spatial-raster/05-Time-Series-Raster/view-stack-histogram-1.png)

It seems like things get green in the spring and summer like we expect, but the 
data at Julian days 277 and 293 are unusual. It appears as if the vegetation got
green in the spring, but then died back only to get green again towards the end 
of the year. Is this right?

### Explore Unusual Data Patterns
The NDVI data that we are using comes from 2011, perhaps a strong freeze around
Julian day 277 could cause a vegetation to senesce early, however in the eastern
United States, it seems unusual that it would proceed to green up again shortly 
thereafter. 

Let's next view some temperature data for our field site to see whether there 
were some unusual fluctuations that may explain this pattern of greening and
browning seen in the NDVI data.

![ ]({{ site.baseurl }}/images/rfigs/R/dc-spatial-raster/05-Time-Series-Raster/view-temp-data-1.png)

There are no significant peaks or dips in the temperature during the late summer
or early fall time period that might account for patterns seen in the NDVI data.

What is our next step? 

Let's have a look at the source Landsat imagery that was partially used used to 
derive our NDVI rasters to try to understand what appears to be outlier NDVI values.

<div id="ds-challenge" markdown="1">
### Challenge: Examine RGB Raster Files

1. View the imagery located in the `/NEON-DS-Landsat-NDVI/HARV/2011` directory. 
2. Plot the RGB images for the Julian days 277 and 293 then plot and compare
those images to jdays 133 and 197.
3. Does the RGB imagery from these two days explain the low NDVI values observed
on these days?  

HINT: if you want to plot 4 images in a tiled set, you can use 
`par(mfrow=c(2,2))` to create a 2x2 tiled layout. When you are done, be sure to
reset your layout using: `par(mfrow=c(1,1))`.

</div>

![ ]({{ site.baseurl }}/images/rfigs/R/dc-spatial-raster/05-Time-Series-Raster/view-all-rgb-1.png)

## Explore The Data's Source
The third challenge question, "Does the RGB imagery from these two days explain 
the low NDVI values observed on these days?" highlights the importance of
exploring the source of a derived data product. In this case, the NDVI data
product was derived from (created using) Landsat imagery - specifically the red
and near-infrared bands.

When we look at the RGB collected at Julian days 277 and 293 we see that most of
the image is filled with clouds. The very low NDVI values resulted from cloud
cover — a common challenge that we encounter when working with satellite remote
sensing imagery.  
