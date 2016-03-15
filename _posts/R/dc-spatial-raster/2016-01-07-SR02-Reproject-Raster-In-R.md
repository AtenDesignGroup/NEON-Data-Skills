---
layout: post
title: "Raster 02: When Rasters Don't Line Up - Reproject Raster Data in R"
date:   2015-10-27
authors: [Leah A. Wasser, Megan A. Jones, Zack Bryn, Kristina Riemer, Jason Williams, Jeff Hollister,  Mike Smorul]
contributors: [ ]
packagesLibraries: [raster, rgdal]
dateCreated:  2015-10-23
lastModified: 2016-03-15
categories:  [self-paced-tutorial]
tags: [R, raster, spatial-data-gis]
tutorialSeries: [raster-data-series]
mainTag: raster-data-series
description: "This tutorial explores issues associated with working with rasters
in different Coordinate Reference Systems (CRS) / projections. When two rasters 
are in different CRS, they will not plot nicely together on a map. We will learn
how to reproject a raster in R using the projectRaster function in the raster 
package."
code1: /R/dc-spatial-raster/02-Reproject-Raster-In-R.R
image:
  feature: NEONCarpentryHeader_2.png
  credit: A collaboration between the National Ecological Observatory Network (NEON) and Data Carpentry
  creditlink:
permalink: /R/Reproject-Raster-In-R/
comments: true
---

{% include _toc.html %}

## About
Sometimes we encounter raster datasets that do not "line up" when plotted or 
analyzed. Rasters that don't line up are most often in different Coordinate
Reference Systems (CRS).

This tutorial explains how to deal with rasters in different, known CRSs. It
will walk though reprojecting rasters in `R` using the `projectRaster()`
function in the `raster` package.

**R Skill Level:** Intermediate - you've got the basics of `R` down.

<div id="objectives" markdown="1">

# Goals / Objectives

After completing this activity, you will:

* Be able to reproject a raster in `R`.

## Things You’ll Need To Complete This Tutorial
You will need the most current version of `R` and, preferably, `RStudio` loaded
on your computer to complete this tutorial.

### Install R Packages

* **raster:** `install.packages("raster")`
* **rgdal:** `install.packages("rgdal")`

* [More on Packages in R - Adapted from Software Carpentry.]({{site.baseurl}}/R/Packages-In-R/)

#### Data to Download
{% include/dataSubsets/_data_Airborne-Remote-Sensing.html %}

****

{% include/_greyBox-wd-rscript.html %}

****

### Additional Resources

* <a href="http://cran.r-project.org/web/packages/raster/raster.pdf" target="_blank">
Read more about the `raster` package in `R`.</a>

</div>

## Raster Projection in R

In the [Plot Raster Data in R]({{ site.baseurl }}/R/Plot-Rasters-In-R/) 
tutorial, we learned how to layer a raster file on top of a hillshade for a nice
looking basemap. In this tutorial, all of our data were in the same CRS. What 
happens when things don't line up?

We will use the `raster` and `rgdal` packages in this tutorial.  


    # load raster package
    library(raster)
    library(rgdal)

Let's create a map of the Harvard Forest Digital Terrain Model 
(`DTM_HARV`) draped or layered on top of the hillshade (`DTM_hill_HARV`).


    # import DTM
    DTM_HARV <- raster("NEON-DS-Airborne-Remote-Sensing/HARV/DTM/HARV_dtmCrop.tif")
    # import DTM hillshade
    DTM_hill_HARV <- 
      raster("NEON-DS-Airborne-Remote-Sensing/HARV/DTM/HARV_DTMhill_WGS84.tif")
    
    # plot hillshade using a grayscale color ramp 
    plot(DTM_hill_HARV,
        col=grey(1:100/100),
        legend=FALSE,
        main="DTM Hillshade\n NEON Harvard Forest Field Site")
    
    # overlay the DTM on top of the hillshade
    plot(DTM_HARV,
         col=terrain.colors(10),
         alpha=0.4,
         add=TRUE,
         legend=FALSE)

![ ]({{ site.baseurl }}/images/rfigs/dc-spatial-raster/02-Reproject-Raster-In-R/import-DTM-hillshade-1.png)

Our results are curious - the Digital Terrain Model (`DTM_HARV`) did not plot on
top of our hillshade. The hillshade plotted just fine on it's own. Let's try to 
plot the DTM on it's own to make sure there are data there.

<i class="fa fa-star"></i> **Code Tip:** For boolean `R` elements, such as
 `add=TRUE`, you can use `T` and `F` in place of `TRUE` and `FALSE`.
{: .notice}


    # Plot DTM 
    plot(DTM_HARV,
         col=terrain.colors(10),
         alpha=1,
         legend=F,
         main="Digital Terrain Model\n NEON Harvard Forest Field Site")

![ ]({{ site.baseurl }}/images/rfigs/dc-spatial-raster/02-Reproject-Raster-In-R/plot-DTM-1.png)

Our DTM seems to contain data and plots just fine. Let's next check the
 Coordinate Reference System (CRS) and compare it to our hillshade.


    # view crs for DTM
    crs(DTM_HARV)

    ## CRS arguments:
    ##  +proj=utm +zone=18 +datum=WGS84 +units=m +no_defs +ellps=WGS84
    ## +towgs84=0,0,0

    # view crs for hillshade
    crs(DTM_hill_HARV)

    ## CRS arguments:
    ##  +proj=longlat +datum=WGS84 +no_defs +ellps=WGS84 +towgs84=0,0,0

Aha! `DTM_HARV` is in the UTM projection. `DTM_hill_HARV` is in
`Geographic WGS84` - which is represented by latitude and longitude values.
Because the two rasters are in different CRSs, they don't line up when plotted
in `R`. We need to *reproject* `DTM_HARV` into the UTM CRS. Alternatively,
we could reproject the hillshade into WGS84. 

## Reproject Rasters
We can use the `projectRaster` function to reproject a raster into a new CRS.
Keep in mind that reprojection only works when you first have a *defined* CRS
for the raster object that you want to reproject. It cannot be used if *no*
CRS is defined. Lucky for us, the `DTM_hill_HARV` has a defined CRS. 

<i class="fa fa-star"></i> **Data Tip:** When we reproject a raster, we 
move it from one "grid" to another. Thus, we are modifying the data! Keep this 
in mind as we work with raster data. 
{: .notice}

To use the `projectRaster` function, we need to define two things:

1. the object we want to reproject and 
2. the CRS that we want to reproject it to. 

The syntax is `projectRaster(RasterObject,crs=CRSToReprojectTo)`

We want the CRS of our hillshade to match the `DTM_HARV` raster. We can thus
assign the CRS of our `DTM_HARV` to our hillshade within the `projectRaster()`
function as follows: `crs=crs(DTM_HARV)`.


    # reproject to UTM
    DTM_hill_UTMZ18N_HARV <- projectRaster(DTM_hill_HARV, 
                                           crs=crs(DTM_HARV))
    
    # compare attributes of DTM_hill_UTMZ18N to DTM_hill
    crs(DTM_hill_UTMZ18N_HARV)

    ## CRS arguments:
    ##  +proj=utm +zone=18 +datum=WGS84 +units=m +no_defs +ellps=WGS84
    ## +towgs84=0,0,0

    crs(DTM_hill_HARV)

    ## CRS arguments:
    ##  +proj=longlat +datum=WGS84 +no_defs +ellps=WGS84 +towgs84=0,0,0

    # compare attributes of DTM_hill_UTMZ18N to DTM_hill
    extent(DTM_hill_UTMZ18N_HARV)

    ## class       : Extent 
    ## xmin        : 731397.3 
    ## xmax        : 733205.3 
    ## ymin        : 4712403 
    ## ymax        : 4713907

    extent(DTM_hill_HARV)

    ## class       : Extent 
    ## xmin        : -72.18192 
    ## xmax        : -72.16061 
    ## ymin        : 42.52941 
    ## ymax        : 42.54234

Notice in the output above that the `crs()` of `DTM_hill_UTMZ18N_HARV` is now
UTM. However, the extent values of `DTM_hillUTMZ18N_HARV` are different from
`DTM_hill_HARV`.

<div id="challenge" markdown="1">
## Challenge: Extent Change with CRS Change
Why do you think the two extents differ?  
</div>




## Deal with Raster Resolution

Let's next have a look at the resolution of our reprojected hillshade.  


    # compare resolution
    res(DTM_hill_UTMZ18N_HARV)

    ## [1] 1.000 0.998

The output resolution of `DTM_hill_UTMZ18N_HARV` is 1 x 0.998. Yet, we know that
the resolution for the data should be 1m x 1m. We can tell `R` to force our
newly reprojected raster to be 1m x 1m resolution by adding a line of code
(`res=`).  


    # adjust the resolution 
    DTM_hill_UTMZ18N_HARV <- projectRaster(DTM_hill_HARV, 
                                      crs=crs(DTM_HARV),
                                      res=1)
    # view resolution
    res(DTM_hill_UTMZ18N_HARV)

    ## [1] 1 1

Let's plot our newly reprojected raster.


    # plot newly reprojected hillshade
    plot(DTM_hill_UTMZ18N_HARV,
        col=grey(1:100/100),
        legend=F,
        main="DTM with Hillshade\n NEON Harvard Forest Field Site")
    
    # overlay the DTM on top of the hillshade
    plot(DTM_HARV,
         col=rainbow(100),
         alpha=0.4,
         add=T,
         legend=F)

![ ]({{ site.baseurl }}/images/rfigs/dc-spatial-raster/02-Reproject-Raster-In-R/plot-projected-raster-1.png)

We have now successfully draped the Digital Terrain Model on top of our
hillshade to produce a nice looking, textured map! 

<div id="challenge" markdown="1">
## Challenge: Reproject, then Plot a Digital Terrain Model 
Create a map of the 
<a href="http://www.neoninc.org/science-design/field-sites/san-joaquin-experimental-range" target="_blank" >San Joaquin Experimental Range</a>
field site using the `SJER_DSMhill_WGS84.tif` and `SJER_dsmCrop.tif` files. 

Reproject the data as necessary to make things line up!
</div>

![ ]({{ site.baseurl }}/images/rfigs/dc-spatial-raster/02-Reproject-Raster-In-R/challenge-code-reprojection-1.png)

<div id="challenge" markdown="1">
If you completed the San Joaquin plotting challenge in the
[Plot Raster Data in R]({{ site.baseurl }}/R/Plot-Rasters-In-R#challenge-create-dtm--dsm-for-sjer) 
tutorial, how does the map you just created compare to that map? 
</div>


