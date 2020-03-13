---
syncID: a82f3466e5e9433fbbf8026a54e720ae
title: "Assignment: Reproducible Workflows with Jupyter Notebooks"
description: "This page details how to complete the assignment for pre-Institute week 3 on documenting your code with Jupyter Notebooks."
dateCreated: 2017-06-12
authors:
contributors: 
estimatedTime:
packagesLibraries:
topics: data-management, rep-sci
languagesTool: Python
dataProduct:
code1:
tutorialSeries: JupPy
urlTitle: jupyter-python-cul-activity

---

This tutorial covers the NEON Pre-Institute Week 3 assignment. If you already
are familiar with Jupyter Notebooks using Python, you may be able to complete the 
assignment without working through the tutorials. 

The goal of the activity this week is simply to ensure that everyone has basic
familiarity with Jupyter Notebooks and that the environment, especially the 
`gdal` package is correctly set up prior to the Data Institute.  

<div id="ds-objectives" markdown="1">

# Deadlines
**Due:** Please submit your activity based notebook files to the
**NEONScience/DI-NEON-participants** GitHub repo as a **pull request**
by 11:59pm on 16 June 2017.

</div>

## Assignment: Open a Tiff File in Jupyter Notebook 

Download the NEON GeoTiFF file of the 
<a href="https://neondata.sharefile.com/d-s9297db4154a4dceb"> digital terrain model (DTM) </a> 
from San Joaquin Experimental Range collected in 2017. 
. 
Open this file in Jupyter Notebooks, determine the size of the raster, and 
(optional extension) plot the raster. Add in both code chunks and text (markdown) chunks
to fully explain what is done. 
When finished, submit the .ipynb file to the 
**NEONScience/DI-NEON-participants** GitHub repo 

## Detailed Directions

### Set up Environment 

First, we will set up the environment as you would need for each of the live 
coding sections of the Data Institute. The following directions are copied over
from the Data Institute Set up Materials.

Note, we've had reports from some individuals that Python 3.6 was able to use 
GDAL, however, we have others who are not able to use Python 3.6 and still have 
to use Python 3.4.  

In your terminal application, navigate to the directory (`cd`) that where you
want the Jupyter Notebooks to be saved (or where they already exist). 

We need to create a new Jupyter kernel for the Python 3.4 conda environment 
(py34) that Jupyter Notebooks will use. 

In your Command Prompt/Terminal, type: 

`python -m ipykernel install --user --name py34 --display-name "Python 3.4 NEON-RSDI"`

In your Command Prompt/Terminal, navigate to the directory (`cd`) that you 
created last week in the GitHub materials. This is where the Jupyter Notebook 
will be saved and the easiest way to access existing notebooks. 

Open Jupyter Notebook with 

`jupyter notebook`

Once the notebook is open, check which version of Python you are in. 

	 # Check what version of Python.  Should be 3.4. 
	 import sys
	 sys.version

To ensure that the correct kernel will operate, navigate to **Kernel** in the menu, 
select **Restart/ClearOutlook**. 

 <figure>
	<a href="{{ site.baseurl }}/images/pre-institute-content/pre-institute3-jupPy/jupPy-kernel.png">
	<img src="{{ site.baseurl }}/images/pre-institute-content/pre-institute3-jupPy/jupPy-kernel.png"></a>
	<figcaption> To ensure that the correct kernel will operate, navigate to 
	Kernel in the menu, select Restart/ClearOutlook.. 
	Source: National Ecological Observatory Network (NEON)  
	</figcaption>
</figure>


You should now be able to work in the notebook. 

The `gdal` package that occasionally has problems with some versions of Python. 
Therefore test out loading it using 

`import gdal`.  

### Download Data 

Download the NEON GeoTiFF file of the 
<a href="https://neondata.sharefile.com/d-s9297db4154a4dceb"> digital terrain model (DTM) </a> 
from San Joaquin Experimental Range collected in 2017.   

### Open the TIFF

As you complete the steps add 1 or 2 markdown sections explaining what you are 
doing with this simple code. 


Place this downloaded file in a repository of your choice (or your current 
working directory). Navigate to that directory. 
	 cd <file-path-here>

Open the file using the `gdal.Open` command.  

	 SJER_DTM_17 = gdal.Open('NEON_D17_SJER_DP3_252000_4109000_DTM.tif')

Check the raster size. 

	  SJER_DTM_17.RasterXSize

If you'd like to also plot the file, feel free to do so.  

Save your file. 


### Push .ipynb to GitHub.  

Submit you file using the GitHub `participants/2017-RemoteSensing/pre-institute3-Jupyter` 
directory. Review the week 2 materials if you have would like a refresher on 
GitHub and the commands associated with adding, commiting, pushing, and making 
a pull request. 


