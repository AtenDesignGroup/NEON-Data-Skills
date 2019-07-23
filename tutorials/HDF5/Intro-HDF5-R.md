---
syncID: 68d6be70d17d4ab9807cd46081e6ee63
title: "Introduction to HDF5 Files in R"
description: "Learn how to build a HDF5 file in R from scratch! Add groups, datasets and attributes. Read data out from the file."
dateCreated: 2015-05-20 15:22:52
authors: Leah A. Wasser
contributors:
estimatedTime: 1.0 - 1.5 Hours
packagesLibraries: rhdf5
topics: HDF5
languagesTool: R, foo
dataProduct:
code1: /HDF5/Intro-HDF5-R.R
tutorialSeries: intro-hdf5-r-series
urlTitle: hdf5-intro-r
---

<div id="ds-objectives" markdown="1">

## Learning Objectives
After completing this tutorial, you will be able to:

* Understand how HDF5 files can be created and structured in R using the 
rhdf5 libraries.
* Understand the three key HDF5 elements: the HDF5 file itself, groups, and datasets.
* Understand how to add and read attributes from an HDF5 file.


## Things You’ll Need To Complete This Tutorial
To complete this tutorial you will need the most current version of R and, 
preferably, RStudio loaded on your computer.

### R Libraries to Install:

* **rhdf5**: The rhdf5 package is hosted on Bioconductor not CRAN. Directions for 
installation are in the first code chunk. 

<a href="{{ site.baseurl }}/packages-in-r" target="_blank"> More on Packages in R </a>

### Data to Download
We will use the file below in the optional challenge activity at the end of this 
tutorial.
{% include/dataSubsets/_data_Field-Site-Spatial-Data.html %}

***
{% include/_greyBox-wd-rscript.html %}
***

### Additional Resources

Consider reviewing the documentation for the 
<a href="http://www.bioconductor.org/packages/release/bioc/manuals/rhdf5/man/rhdf5.pdf" target="_blank">RHDF5 package</a>.

</div>


## About HDF5
The HDF5 file can store large, heterogeneous datasets that include metadata. It 
also supports efficient `data slicing`, or extraction of particular subsets of a 
dataset which means that you don't have to read  large files read into the 
computers memory / RAM in their entirety in order work with them. 

<a href="{{ site.baseurl }}/about-hdf5" target="_blank">Read more about HDF5 here.</a>

## HDF5 in R

To access HDF5 files in R, we will use the `rhdf5` library which is part of 
the <a href="http://www.bioconductor.org" target="_blank">Bioconductor</a> 
suite of R libraries. It might also be useful to install 
<a href="http://www.hdfgroup.org/products/java/hdfview/" target="_blank">the 
free HDF5 viewer</a> which will allow you to explore the contents of an HDF5 file 
using a graphic interface.

More about working with <a href="{{ site.baseurl }}/explore-data-hdfview" target="_blank">HDFview and a hands-on activity here.


First, let's get R setup. We will use the rhdf5 library. To access HDF5 files in 
R, we will use the rhdf5 library which is part of the Bioconductor suite of R 
packages. As of Aug. 2017 this package was not yet on CRAN. 


    # Install rhdf5 package (only need to run if not already installed)
    #install.packages("BiocManager")
    #BiocManager::install("rhdf5")
    
    # Call the R HDF5 Library
    library("rhdf5")
    
    # set working directory to ensure R can find the file we wish to import and where
    # we want to save our files
    #setwd("working-dir-path-here")

Read more about the 
<a href="http://www.bioconductor.org/packages/release/bioc/vignettes/rhdf5/inst/doc/rhdf5.pdf" target="_blank"> `rhdf5` package here.</a>

## Create an HDF5 File in R

Now, let's create a basic H5 file with one group and one dataset in it. 


    # Create hdf5 file
    h5createFile("vegData.h5")

    ## [1] TRUE

    h5createFile()

    ## Error in h5createFile(): argument "file" is missing, with no default

    # create a group called aNEONSite within the H5 file
    h5createGroup("vegData.h5", "aNEONSite")

    ## [1] TRUE

    # view the structure of the h5 we've created
    h5ls("vegData.h5")

    ##   group      name     otype dclass dim
    ## 0     / aNEONSite H5I_GROUP

Next, let's create some dummy data to add to our H5 file.


    # create some sample, numeric data 
    a <- rnorm(n=40, m=1, sd=1) 
    someData <- matrix(a,nrow=20,ncol=2)

Write the sample data to the H5 file.


    # add some sample data to the H5 file located in the aNEONSite group
    # we'll call the dataset "temperature"
    h5write(someData, file = "vegData.h5", name="aNEONSite/temperature")
    
    # let's check out the H5 structure again
    h5ls("vegData.h5")

    ##        group        name       otype dclass    dim
    ## 0          /   aNEONSite   H5I_GROUP              
    ## 1 /aNEONSite temperature H5I_DATASET  FLOAT 20 x 2

View a "dump" of the entire HDF5 file. NOTE: use this command with CAUTION if you
are working with larger datasets!


    # we can look at everything too 
    # but be cautious using this command!
    h5dump("vegData.h5")

    ## $aNEONSite
    ## $aNEONSite$temperature
    ##              [,1]       [,2]
    ##  [1,] -0.16420666  1.5755612
    ##  [2,]  1.80158536  2.1764389
    ##  [3,]  1.23803416  1.2927562
    ##  [4,]  2.05403693  0.1443593
    ##  [5,]  2.23946103  2.1377107
    ##  [6,]  1.43705166  1.5531153
    ##  [7,]  0.46404894  2.1324506
    ##  [8,]  0.16278415  0.3859432
    ##  [9,]  2.79255098  0.3576511
    ## [10,]  1.55760076  1.0051388
    ## [11,]  0.98733027 -0.2242752
    ## [12,]  2.42304247  0.9985014
    ## [13,]  0.38643231  0.3005682
    ## [14,] -1.33153535  1.3499627
    ## [15,]  1.20405715  1.3355688
    ## [16,] -0.08070397  1.7708422
    ## [17,]  3.11366962  1.1097622
    ## [18,]  0.17930508  0.8119678
    ## [19,]  0.87397956  1.7728203
    ## [20,]  1.35663142  1.2177682

    # Close the file. This is good practice.
    H5close()


## Add Metadata (attributes)

Let's add some metadata (called attributes in HDF5 land) to our dummy temperature
data. First, open up the file.


    # open the file, create a class
    fid <- H5Fopen("vegData.h5")
    # open up the dataset to add attributes to, as a class
    did <- H5Dopen(fid, "aNEONSite/temperature")
    
    # Provide the NAME and the ATTR (what the attribute says) for the attribute.
    h5writeAttribute(did, attr="Here is a description of the data",
                     name="Description")
    h5writeAttribute(did, attr="Meters",
                     name="Units")

Now we can add some attributes to the file. 


    # let's add some attributes to the group
    did2 <- H5Gopen(fid, "aNEONSite/")
    
    h5writeAttribute(did2, attr="San Joaquin Experimental Range",
                     name="SiteName")
    
    h5writeAttribute(did2, attr="Southern California",
                     name="Location")
    
    # close the files, groups and the dataset when you're done writing to them!
    H5Dclose(did)
    
    H5Gclose(did2)
    
    H5Fclose(fid)

## Working with an HDF5 File in R

Now that we've created our H5 file, let's use it! First, let's have a look at 
the attributes of the dataset and group in the file.


    # look at the attributes of the precip_data dataset
    h5readAttributes(file = "vegData.h5", 
                     name = "aNEONSite/temperature")

    ## $Description
    ## [1] "Here is a description of the data"
    ## 
    ## $Units
    ## [1] "Meters"

    # look at the attributes of the aNEONsite group
    h5readAttributes(file = "vegData.h5", 
                     name = "aNEONSite")

    ## $Location
    ## [1] "Southern California"
    ## 
    ## $SiteName
    ## [1] "San Joaquin Experimental Range"

    # let's grab some data from the H5 file
    testSubset <- h5read(file = "vegData.h5", 
                     name = "aNEONSite/temperature")
    
    testSubset2 <- h5read(file = "vegData.h5", 
                     name = "aNEONSite/temperature",
                     index=list(NULL,1))
    H5close()

Once we've extracted data from our H5 file, we can work with it
in R. 


    # create a quick plot of the data
    hist(testSubset2)

![ ]({{ site.baseurl }}/images/rfigs/HDF5/Intro-HDF5-R/access-plot-data-1.png)

 <div id="ds-challenge" markdown="1">
### Challenge: Work with HDF5 Files

Time to practice the skills you've learned. Open up the D17_2013_SJER_vegStr.csv 
in R. 

* Create a new HDF5 file called `vegStructure`.
* Add a group in your HDF5 file called `SJER`. 
* Add the veg structure data to that folder.
* Add some attributes the SJER group and to the data. 
* Now, repeat the above with the D17_2013_SOAP_vegStr csv.
* Name your second group SOAP

Hint: `read.csv()` is a good way to read in .csv files. 

</div>



