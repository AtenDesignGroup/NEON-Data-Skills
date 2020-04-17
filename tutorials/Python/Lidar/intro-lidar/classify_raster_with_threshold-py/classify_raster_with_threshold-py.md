---
syncID: c324c554a35f463493349dbd0be19cec
title: "Classify a Raster Using Threshold Values in Python - 2017"
description: "Learn how to read NEON lidar raster GeoTIFFs (e.g., CHM, slope, aspect) into Python numpy arrays with gdal and create a classified raster object." 
dateCreated: 2017-06-21 
authors: Bridget Hass
contributors: 
estimatedTime: 
packagesLibraries: numpy, gdal, matplotlib, matplotlib.pyplot, os
topics: lidar, raster, remote-sensing
languagesTool: python
dataProduct: DP1.30003, DP3.30015, DP3.30024, DP3.30025
code1: Python/remote-sensing/lidar/classify_raster_with_threshold_py.ipynb
tutorialSeries: intro-lidar-py-series
urlTitle: classify-raster-thresholds-py
---

In this tutorial, we will learn how to read NEON lidar raster GeoTIFFS 
(e.g., CHM, slope aspect) into Python `numpy` arrays with gdal and create a 
classified raster object.

<div id="ds-objectives" markdown="1">

### Objectives

After completing this tutorial, you will be able to:

* Read NEON lidar raster GeoTIFFS (e.g., CHM, slope aspect) into Python numpy arrays with gdal.
* Create a classified raster object.

### Install Python Packages

* **numpy**
* **gdal** 
* **matplotlib** 

### Download Data

<h3> <a href="https://neondata.sharefile.com/d-s11d5c8b9c53426db"> NEON Teaching Data Subset: Data Institute 2017 Data Set</a></h3> 

To complete this tutorial, you will use data available from the NEON 2017 Data
Institute teaching dataset available for download. 

Caution: This dataset includes all the data for the 2017 Data Institute, 
including hyperspectral and lidar datasets and is therefore a large file (12 GB). 
Ensure that you have sufficient space on your 
hard drive before you begin the download. If not, download to an external 
hard drive and make sure to correct for the change in file path when working 
through the tutorial.

The LiDAR and imagery data used to create this raster teaching data subset 
were collected over the 
<a href="http://www.neonscience.org/" target="_blank"> National Ecological Observatory Network's</a> 
<a href="http://www.neonscience.org/science-design/field-sites/" target="_blank" >field sites</a>
and processed at NEON headquarters.
The entire dataset can be accessed on the 
<a href="http://data.neonscience.org" target="_blank"> NEON data portal</a>.

<a href="https://neondata.sharefile.com/d-s11d5c8b9c53426db" class="link--button link--arrow">
Download Dataset</a>






</div>


First, let's import the required packages and set our plot display to be in line. 


```python
import numpy as np
import gdal
import matplotlib.pyplot as plt
%matplotlib inline
import warnings
warnings.filterwarnings('ignore')
```

### Open a Geotif with GDAL

Let's look at the SERC Canopy Height Model (CHM) to start. We can open and read 
this in Python using the `gdal.Open` function.


```python
chm_filename = '../data/SERC/lidar/2016_SERC_1_367000_4306000_pit_free_CHM.tif'
chm_dataset = gdal.Open(chm_filename)
```

### Read information from GeoTIFF Tags
The GeoTIFF file format comes with associated metadata containing information 
about the location and coordinate system/projection. Once we have read in the 
dataset, we can access this information with the following commands.


```python
#Display the dataset dimensions, number of bands, driver, and geotransform 
cols = chm_dataset.RasterXSize; print('# of columns:',cols)
rows = chm_dataset.RasterYSize; print('# of rows:',rows)
print('# of bands:',chm_dataset.RasterCount)
print('driver:',chm_dataset.GetDriver().LongName)
```

    # of columns: 1000
    # of rows: 1000
    # of bands: 1
    driver: GeoTIFF
    

### GetProjection 
We can use `GetProjection` to see information about the coordinate system and 
EPSG code. 


```python
print('projection:',chm_dataset.GetProjection())
```

    projection: PROJCS["WGS 84 / UTM zone 18N",GEOGCS["WGS 84",DATUM["WGS_1984",SPHEROID["WGS 84",6378137,298.257223563,AUTHORITY["EPSG","7030"]],AUTHORITY["EPSG","6326"]],PRIMEM["Greenwich",0],UNIT["degree",0.0174532925199433],AUTHORITY["EPSG","4326"]],PROJECTION["Transverse_Mercator"],PARAMETER["latitude_of_origin",0],PARAMETER["central_meridian",-75],PARAMETER["scale_factor",0.9996],PARAMETER["false_easting",500000],PARAMETER["false_northing",0],UNIT["metre",1,AUTHORITY["EPSG","9001"]],AUTHORITY["EPSG","32618"]]
    

### GetGeoTransform

The `geotransform` contains information about the origin (upper-left corner) of 
the raster, the pixel size, and the rotation angle of the data. All NEON data 
in the latest format have zero rotation. In this example, the values correspond 
to: 


```python
print('geotransform:',chm_dataset.GetGeoTransform())
```

    geotransform: (367000.0, 1.0, 0.0, 4307000.0, 0.0, -1.0)
    

In this case, the geotransform values correspond to:

1. Left-Most X Coordinate = `367000.0`
2. W-E Pixel Resolution = `1.0`
3. Rotation (0 if Image is North-Up) = `0.0`
4. Upper Y Coordinate = `4307000.0`
5. Rotation (0 if Image is North-Up) = `0.0`
6. N-S Pixel Resolution = `-1.0` 

We can convert this information into a spatial extent (xMin, xMax, yMin, yMax) 
by combining information about the origin, number of columns & rows, and pixel 
size, as follows.


```python
chm_mapinfo = chm_dataset.GetGeoTransform()
xMin = chm_mapinfo[0]
yMax = chm_mapinfo[3]

xMax = xMin + chm_dataset.RasterXSize/chm_mapinfo[1] #divide by pixel width 
yMin = yMax + chm_dataset.RasterYSize/chm_mapinfo[5] #divide by pixel height (note sign +/-)
chm_ext = (xMin,xMax,yMin,yMax)
print('chm raster extent:',chm_ext)
```

    chm raster extent: (367000.0, 368000.0, 4306000.0, 4307000.0)
    

### GetRasterBand 
We can read in a single raster band with ```GetRasterBand``` and access 
information about this raster band such as the No Data Value, Scale Factor, 
and Statitiscs as follows.


```python
chm_raster = chm_dataset.GetRasterBand(1)
noDataVal = chm_raster.GetNoDataValue(); print('no data value:',noDataVal)
scaleFactor = chm_raster.GetScale(); print('scale factor:',scaleFactor)
chm_stats = chm_raster.GetStatistics(True,True)
print('SERC CHM Statistics: Minimum=%.2f, Maximum=%.2f, Mean=%.3f, StDev=%.3f' % 
      (chm_stats[0], chm_stats[1], chm_stats[2], chm_stats[3]))
```

    no data value: -9999.0
    scale factor: 1.0
    SERC CHM Statistics: Minimum=0.00, Maximum=40.67, Mean=7.617, StDev=10.785
    

### ReadAsArray

Finally we can convert the raster to an array using the ReadAsArray command. 
Use the extension ```astype(np.float)``` to ensure the array contains 
floating-point numbers. Once we generate the array, we want to set No Data 
Values to NaN, and apply the scale factor: 


```python
chm_array = chm_dataset.GetRasterBand(1).ReadAsArray(0,0,cols,rows).astype(np.float)
chm_array[chm_array==int(noDataVal)]=np.nan #Assign CHM No Data Values to NaN
chm_array=chm_array/scaleFactor
print('SERC CHM Array:\n',chm_array) #display array values
```

    SERC CHM Array:
     [[ 22.60000038  24.29999924  26.92000008 ...,  15.81999969  16.68000031
       16.20000076]
     [ 23.18000031  25.22999954  25.62000084 ...,  13.86999989  12.84000015
       12.17000008]
     [ 25.19000053  26.          26.29000092 ...,  12.38000011  12.10999966
       12.23999977]
     ..., 
     [  0.           0.           0.         ...,  25.53000069  25.12000084
       26.34000015]
     [  0.           0.           0.         ...,  26.57999992  26.14999962
       25.94000053]
     [  0.           0.           0.         ...,  26.12999916  25.85000038
       25.54000092]]
    

### Array Statistics 

To get a better idea of the dataset, print some basic statistics.


```python
# Display statistics (min, max, mean); numpy.nanmin calculates the minimum without the NaN values.
print('SERC CHM Array Statistics:')
print('min:',round(np.nanmin(chm_array),2))
print('max:',round(np.nanmax(chm_array),2))
print('mean:',round(np.nanmean(chm_array),2))

# Calculate the % of pixels that are NaN and non-zero:
pct_nan = np.count_nonzero(np.isnan(chm_array))/(rows*cols)
print('% NaN:',round(pct_nan*100,2))
print('% non-zero:',round(100*np.count_nonzero(chm_array)/(rows*cols),2))
```

    SERC CHM Array Statistics:
    min: 0.0
    max: 41.31
    mean: 7.6
    % NaN: 0.0
    % non-zero: 39.5
    


```python
# Define the plot_band_array function from Day 1
def plot_band_array(band_array,refl_extent,colorlimit,ax=plt.gca(),title='',cbar ='on',cmap_title='',colormap='spectral'):
    plot = plt.imshow(band_array,extent=refl_extent,clim=colorlimit); 
    if cbar == 'on':
        cbar = plt.colorbar(plot,aspect=40); plt.set_cmap(colormap); 
        cbar.set_label(cmap_title,rotation=90,labelpad=20);
    plt.title(title); ax = plt.gca(); 
    ax.ticklabel_format(useOffset=False, style='plain'); #do not use scientific notation #
    rotatexlabels = plt.setp(ax.get_xticklabels(),rotation=90); #rotate x tick labels 90 degrees
```


```python
plot_band_array(chm_array,chm_ext,(0,80),title='SERC Canopy Height',cmap_title='Canopy Height, m')
```

![ ](https://raw.githubusercontent.com/NEONScience/NEON-Data-Skills/dev-aten/graphics/py-figs/classify-raster-thresholds/output_19_0.png)


### Plot Histogram of Data 


```python
import copy
chm_nonan_array = copy.copy(chm_array)
chm_nonan_array = chm_nonan_array[~np.isnan(chm_array)]
plt.hist(chm_nonan_array,weights=np.zeros_like(chm_nonan_array)+1./
         (chm_array.shape[0]*chm_array.shape[1]),bins=50);
plt.title('Distribution of SERC Canopy Height')
plt.xlabel('Tree Height (m)'); plt.ylabel('Relative Frequency')
```

    <matplotlib.text.Text at 0x95a12e8>


![ ](https://raw.githubusercontent.com/NEONScience/NEON-Data-Skills/dev-aten/graphics/py-figs/classify-raster-thresholds/output_21_1.png)


We can see that most of the values are zero. In SERC, many of the zero CHM 
values correspond to bodies of water as well as regions of land without trees. 
Let's look at a  histogram and plot the data without zero values.


```python
chm_nonzero_array = copy.copy(chm_array)
chm_nonzero_array[chm_array==0]=np.nan
chm_nonzero_nonan_array = chm_nonzero_array[~np.isnan(chm_nonzero_array)]
# Use weighting to plot relative frequency
plt.hist(chm_nonzero_nonan_array,weights=np.zeros_like(chm_nonzero_nonan_array)+1./
         (chm_array.shape[0]*chm_array.shape[1]),bins=50);

# plt.hist(chm_nonzero_nonan_array.flatten(),50) 
plt.title('Distribution of SERC Non-Zero Canopy Height')
plt.xlabel('Tree Height (m)'); plt.ylabel('Relative Frequency')
# plt.xlim(0,25); plt.ylim(0,4000000)

print('min:',np.amin(chm_nonzero_nonan_array),'m')
print('max:',round(np.amax(chm_nonzero_nonan_array),2),'m')
print('mean:',round(np.mean(chm_nonzero_nonan_array),2),'m')
```

    min: 2.0 m
    max: 41.31 m
    mean: 19.23 m
    

![ ](https://raw.githubusercontent.com/NEONScience/NEON-Data-Skills/dev-aten/graphics/py-figs/classify-raster-thresholds/output_23_1.png)

From the histogram we can see that the majority of the trees are < 45m. We can 
replot the CHM array, this time adjusting the color bar to better visualize the 
variation in canopy height. We will plot the non-zero array so that CHM=0 
appears white. 


```python
plot_band_array(chm_array,chm_ext,(0,45),title='SERC Canopy Height',cmap_title='Canopy Height, m',colormap='BuGn')
```

![ ](https://raw.githubusercontent.com/NEONScience/NEON-Data-Skills/dev-aten/graphics/py-figs/classify-raster-thresholds/output_25_0.png)


### Read in a GeoTIFF raster and associated metadata

Now that we have a basic understanding of how GDAL reads in a GeoTIFF file, we 
can write a function to read in a NEON geotif, convert it to a numpy array, and 
store the associated metadata in a Python dictionary in order to more efficiently 
carry out further analysis.


```python
# raster2array.py reads in the first band of geotif file and returns an array and associated 
# metadata dictionary

from osgeo import gdal
import numpy as np

def raster2array(geotif_file):
    metadata = {}
    dataset = gdal.Open(geotif_file)
    metadata['array_rows'] = dataset.RasterYSize
    metadata['array_cols'] = dataset.RasterXSize
    metadata['bands'] = dataset.RasterCount
    metadata['driver'] = dataset.GetDriver().LongName
    metadata['projection'] = dataset.GetProjection()
    metadata['geotransform'] = dataset.GetGeoTransform()
    
    mapinfo = dataset.GetGeoTransform()
    metadata['pixelWidth'] = mapinfo[1]
    metadata['pixelHeight'] = mapinfo[5]

    metadata['ext_dict'] = {}
    metadata['ext_dict']['xMin'] = mapinfo[0]
    metadata['ext_dict']['xMax'] = mapinfo[0] + dataset.RasterXSize/mapinfo[1]
    metadata['ext_dict']['yMin'] = mapinfo[3] + dataset.RasterYSize/mapinfo[5]
    metadata['ext_dict']['yMax'] = mapinfo[3]
    
    metadata['extent'] = (metadata['ext_dict']['xMin'],metadata['ext_dict']['xMax'],
                          metadata['ext_dict']['yMin'],metadata['ext_dict']['yMax'])
    
    if metadata['bands'] == 1:
        raster = dataset.GetRasterBand(1)
        metadata['noDataValue'] = raster.GetNoDataValue()
        metadata['scaleFactor'] = raster.GetScale()
        
        # band statistics
        metadata['bandstats'] = {} #make a nested dictionary to store band stats in same 
        stats = raster.GetStatistics(True,True)
        metadata['bandstats']['min'] = round(stats[0],2)
        metadata['bandstats']['max'] = round(stats[1],2)
        metadata['bandstats']['mean'] = round(stats[2],2)
        metadata['bandstats']['stdev'] = round(stats[3],2)
        
        array = dataset.GetRasterBand(1).ReadAsArray(0,0,metadata['array_cols'],metadata['array_rows']).astype(np.float)
        array[array==int(metadata['noDataValue'])]=np.nan
        array = array/metadata['scaleFactor']
        return array, metadata
    
    elif metadata['bands'] > 1:
        print('More than one band ... need to modify function for case of multiple bands')
```


```python
SERC_chm_array, SERC_chm_metadata = raster2array('../data/SERC/lidar/SERC_CHM.tif')

print('SERC CHM Array:\n',SERC_chm_array)

#print metadata in alphabetical order
for item in sorted(SERC_chm_metadata):
    print(item + ':', SERC_chm_metadata[item])
```

    SERC CHM Array:
     [[ nan  nan  nan ...,  nan  nan  nan]
     [ nan  nan  nan ...,  nan  nan  nan]
     [ nan  nan  nan ...,  nan  nan  nan]
     ..., 
     [ nan  nan  nan ...,  nan  nan  nan]
     [ nan  nan  nan ...,  nan  nan  nan]
     [ nan  nan  nan ...,  nan  nan  nan]]
    array_cols: 11197
    array_rows: 14997
    bands: 1
    bandstats: {'stdev': 12.54, 'mean': 10.66, 'max': 48.45, 'min': 0.0}
    driver: GeoTIFF
    ext_dict: {'yMin': 4298479.0, 'xMin': 358816.0, 'xMax': 370013.0, 'yMax': 4313476.0}
    extent: (358816.0, 370013.0, 4298479.0, 4313476.0)
    geotransform: (358816.0, 1.0, 0.0, 4313476.0, 0.0, -1.0)
    noDataValue: -9999.0
    pixelHeight: -1.0
    pixelWidth: 1.0
    projection: PROJCS["WGS 84 / UTM zone 18N",GEOGCS["WGS 84",DATUM["WGS_1984",SPHEROID["WGS 84",6378137,298.257223563,AUTHORITY["EPSG","7030"]],AUTHORITY["EPSG","6326"]],PRIMEM["Greenwich",0],UNIT["degree",0.0174532925199433],AUTHORITY["EPSG","4326"]],PROJECTION["Transverse_Mercator"],PARAMETER["latitude_of_origin",0],PARAMETER["central_meridian",-75],PARAMETER["scale_factor",0.9996],PARAMETER["false_easting",500000],PARAMETER["false_northing",0],UNIT["metre",1,AUTHORITY["EPSG","9001"]],AUTHORITY["EPSG","32618"]]
    scaleFactor: 1.0
    

## Threshold Based Raster Classification
Next, we will create a classified raster object. To do this, we will use the se 
the numpy.where function to create a new raster based off boolean classifications. 
Let's classify the canopy height into four groups:

* Class 1: **CHM = 0 m** 
* Class 2: **0m < CHM <= 20m**
* Class 3: **20m < CHM <= 40m**
* Class 4: **CHM > 40m**


```python
chm_reclass = copy.copy(chm_array)
chm_reclass[np.where(chm_array==0)] = 1 # CHM = 0 : Class 1
chm_reclass[np.where((chm_array>0) & (chm_array<=20))] = 2 # 0m < CHM <= 20m - Class 2
chm_reclass[np.where((chm_array>20) & (chm_array<=40))] = 3 # 20m < CHM < 40m - Class 3
chm_reclass[np.where(chm_array>40)] = 4 # CHM > 40m - Class 4

print('Min:',np.nanmin(chm_reclass))
print('Max:',np.nanmax(chm_reclass))
print('Mean:',round(np.nanmean(chm_reclass),2))

import matplotlib.colors as colors
plt.figure(); #ax = plt.subplots()
cmapCHM = colors.ListedColormap(['lightblue','yellow','green','red'])
plt.imshow(chm_reclass,extent=chm_ext,cmap=cmapCHM)
plt.title('SERC CHM Classification')
ax=plt.gca(); ax.ticklabel_format(useOffset=False, style='plain') #do not use scientific notation 
rotatexlabels = plt.setp(ax.get_xticklabels(),rotation=90) #rotate x tick labels 90 degrees
# forceAspect(ax,aspect=1) # ax.set_aspect('auto')

# Create custom legend to label the four canopy height classes:
import matplotlib.patches as mpatches
class1_box = mpatches.Patch(color='lightblue', label='CHM = 0m')
class2_box = mpatches.Patch(color='yellow', label='0m < CHM < 20m')
class3_box = mpatches.Patch(color='green', label='20m < CHM < 40m')
class4_box = mpatches.Patch(color='red', label='CHM > 40m')

ax.legend(handles=[class1_box,class2_box,class3_box,class4_box],
          handlelength=0.7,bbox_to_anchor=(1.05, 0.4),loc='lower left',borderaxespad=0.)
```

    Min: 1.0
    Max: 4.0
    Mean: 1.59
    

    <matplotlib.legend.Legend at 0xc877240>


![ ](https://raw.githubusercontent.com/NEONScience/NEON-Data-Skills/dev-aten/graphics/py-figs/classify-raster-thresholds/output_30_2.png)


<div id="ds-challenge" markdown="1">

**Challenge: Document Your Workflow**

1. Look at the code that you created for this tutorials. Now imagine yourself 
months in the future. Comment your script (Markdown sections and code comments) 
so that your methods and process is clear and reproducible for yourself or 
others to follow in the future. 
2. In commenting your script, synthesize and interpret the outputs. Do they tell you anything 
about the vegetation structure at the field site? 

</div>


<div id="ds-challenge" markdown="1">

**Challenge: Try out other Classifications**

Create the following threshold classified outputs:

1. A raster where NDVI values are classified into the following categories:
    * Low greenness: NDVI < 0.3
    * Medium greenness: 0.3 < NDVI < 0.6
    * High greenness: NDVI > 0.6
2. A raster where aspect is classified into North and South facing slopes. 

Be sure to document your workflow as you go using a combination of Jupyter 
Markdown cells and code comments. When you are finished, explore your outputs to 
HTML by selecting File > Download As > HTML (.html). 

Data Institute Participants: Save the file as LastName_Tues_classifyThreshold.html. 
Add this to the Tuesday materials directory in the **DI17-NEON-participants** GitHub
repo. Remember to push from your local repo to your fork, and then complete the 
Pull Request to the central repository. 

</div>


## Aspect Raster Classification on TEAK Dataset (California)

Next, we will create a classified raster object based on slope using the TEAK 
dataset. This time, our classifications will be:
- **North Facing Slopes**: 0-45 & 315-360 degrees ; class=1
- **South Facing Slopes**: 135-225 degrees ; class=2
- **East & West Facing Slopes**: 45-135 & 225-315 degrees ; unclassified


 <figure>
	<a href="https://raw.githubusercontent.com/NEONScience/NEON-Data-Skills/dev-aten/graphics/lidar/NSEWclassification_BozEtAl2015.jpg">
	<img src="https://raw.githubusercontent.com/NEONScience/NEON-Data-Skills/dev-aten/graphics/lidar/NSEWclassification_BozEtAl2015.jpg"></a>
	<figcaption> Reclassification of aspect (azimuth) values: North, 315-45 
	degrees; East, 45-135 degrees; South, 135-225 degrees; West, 225-315 degrees.
	Source: <a href="http://www.aimspress.com/article/10.3934/energy.2015.3.401/fulltext.html"> Boz et al. 2015 </a>
	</figcaption>
</figure>


**Further Reading:** 
There are a range of applications for aspect classification. 
<a href="http://www.aimspress.com/article/10.3934/energy.2015.3.401/fulltext.html" target="_blank">Boz et al. (2015) </a> 
provide an example of classifying LiDAR aspect data to determine suitability of 
roofs for PV (photovoltaic) systems. Can you think of any other applications 
where aspect classification might be useful?

<div id="ds-dataTip" markdown="1"><i class="fa fa-star"></i> **Data Tip:** You can calculate aspect in Python from 
a digital elevation (or surface) model using the pyDEM package from the 
<a href="https://earthlab.github.io/tutorials/get-slope-aspect-from-digital-elevation-model/" target="_blank">CU-Boulder
Earth Lab.</a>
</div>



### Import Data

First, we can import the TEAK aspect raster GeoTIFF and convert it to an array 
using the `raster2array` function.


```python
TEAK_aspect_tif = '../data/TEAK/lidar/2013_TEAK_1_326000_4103000_DTM_aspect.tif'
TEAK_asp_array, TEAK_asp_metadata = raster2array(TEAK_aspect_tif)

#print metadata in alphabetical order
for item in sorted(TEAK_asp_metadata):
    print(item + ':', TEAK_asp_metadata[item])

plot_band_array(TEAK_asp_array,TEAK_asp_metadata['extent'],(0,360),title='TEAK Aspect',cmap_title='Aspect, deg')
```

    array_cols: 1000
    array_rows: 1000
    bands: 1
    bandstats: {'stdev': 66.57, 'mean': 115.59, 'max': 359.99, 'min': 0.0}
    driver: GeoTIFF
    ext_dict: {'yMin': 4103000.0, 'xMin': 326000.0, 'xMax': 327000.0, 'yMax': 4104000.0}
    extent: (326000.0, 327000.0, 4103000.0, 4104000.0)
    geotransform: (326000.0, 1.0, 0.0, 4104000.0, 0.0, -1.0)
    noDataValue: -9999.0
    pixelHeight: -1.0
    pixelWidth: 1.0
    projection: PROJCS["WGS 84 / UTM zone 11N",GEOGCS["WGS 84",DATUM["WGS_1984",SPHEROID["WGS 84",6378137,298.257223563,AUTHORITY["EPSG","7030"]],AUTHORITY["EPSG","6326"]],PRIMEM["Greenwich",0],UNIT["degree",0.0174532925199433],AUTHORITY["EPSG","4326"]],PROJECTION["Transverse_Mercator"],PARAMETER["latitude_of_origin",0],PARAMETER["central_meridian",-117],PARAMETER["scale_factor",0.9996],PARAMETER["false_easting",500000],PARAMETER["false_northing",0],UNIT["metre",1,AUTHORITY["EPSG","9001"]],AUTHORITY["EPSG","32611"]]
    scaleFactor: 1.0
    

![ ](https://raw.githubusercontent.com/NEONScience/NEON-Data-Skills/dev-aten/graphics/py-figs/classify-raster-thresholds/output_33_1.png)


```python
aspect_array = copy.copy(TEAK_asp_array)
asp_reclass = copy.copy(aspect_array)
asp_reclass[np.where(((aspect_array>=0) & (aspect_array<=45)) | (aspect_array>=315))] = 1 #North - Class 1
asp_reclass[np.where((aspect_array>=135) & (aspect_array<=225))] = 2 #South - Class 2
asp_reclass[np.where(((aspect_array>45) & (aspect_array<135)) | ((aspect_array>225) & (aspect_array<315)))] = np.nan #W & E - Unclassified

print('Min:',np.nanmin(asp_reclass))
print('Max:',np.nanmax(asp_reclass))
print('Mean:',round(np.nanmean(asp_reclass),2))

# Scale plot 
def forceAspect(ax,aspect=1):
    im = ax.get_images()
    extent =  im[0].get_extent()
    ax.set_aspect(abs((extent[1]-extent[0])/(extent[3]-extent[2]))/aspect)

# plot_band_array(aspect_reclassified,asp_ext,'North and South Facing Slopes \n TEAK')
from matplotlib import colors
fig, ax = plt.subplots()
cmapNS = colors.ListedColormap(['blue','red'])
plt.imshow(asp_reclass,extent=TEAK_asp_metadata['extent'],cmap=cmapNS)
plt.title('TEAK \n N and S Facing Slopes')
ax=plt.gca(); ax.ticklabel_format(useOffset=False, style='plain') #do not use scientific notation 
rotatexlabels = plt.setp(ax.get_xticklabels(),rotation=90) #rotate x tick labels 90 degrees
ax = plt.gca(); forceAspect(ax,aspect=1)

# Create custom legend to label N & S
import matplotlib.patches as mpatches
blue_box = mpatches.Patch(color='blue', label='North')
red_box = mpatches.Patch(color='red', label='South')
ax.legend(handles=[blue_box,red_box],handlelength=0.7,bbox_to_anchor=(1.05, 0.45), 
          loc='lower left', borderaxespad=0.)
```

    Min: 1.0
    Max: 2.0
    Mean: 1.7
    

    <matplotlib.legend.Legend at 0xc02c748>



![ ](https://raw.githubusercontent.com/NEONScience/NEON-Data-Skills/dev-aten/graphics/py-figs/classify-raster-thresholds/output_34_2.png)


<div id="ds-challenge" markdown="1">
**Challenge: Add docstrings**

Clear code is important -- with this in mind add docstrings to the neon_aop_lidar_raster_functions.py 
module. 

1. Include a description of the module and a list of the functions it contains. 
1. Include the following information for each function. (Refer to 
<a href="http://www.python.org/dev/peps/pep-0257/" target="_blank"> the Python Developer's Guide on documentstrings</a> 
for more information.) Use triple quotes to start and end a doc string ( """ docstring here """)

* description of the function 
* parameters (inputs)
* returns (outputs)
* See also ( list & briefly describe related functions)
* Notes
* Examples (how to execute the function. 

 
1. Save the updated commented module to a python .py file from Jupyter using the 
magic command: %%writefile neon_aop_lidar_raster_fucntions.py.  Alternatively, you 
can copy it into a text editor or Spyder (a Python IDE) and save it as a .py file. 
1. Once saved and re-loaded, use the `help(function)` or `function?` to view the 
docstrings you wrote. 

</div>





