FileInfo
===========

A Plugin for moziloCMS 2.0

Generates a download link and optional information like type, size and number of downloads.

## Installation
#### With moziloCMS installer
To add (or update) a plugin in moziloCMS, go to the backend tab *Plugins* and click the item *Manage Plugins*. Here you can choose the plugin archive file (note that it has to be a ZIP file with exactly the same name the plugin has) and click *Install*. Now the FileInfo plugin is listed below and can be activated.

#### Manually
Installing a plugin manually requires FTP Access. 
- Upload unpacked plugin folder into moziloCMS plugin directory: ```/<moziloroot>/plugins/```
- Set default permissions (chmod 777 for folders and 666 for files)
- Go to the backend tab *Plugins* and activate the now listed new FileInfo plugin

## Syntax
```{FileInfo|<file>|<template>|<linktext>}```
Here comes the general description of the plugin tag.

1. Parameter ```<file>```: Short description of parameter 1.
2. Parameter ```<template>```: Short description of parameter 2.
3. Parameter ```<linktext>```: Short description of parameter 2.

## License
This Plugin is distributed under *GNU General Public License, Version 3* (see LICENSE).

## Documentation
A detailed documentation and demo can be found on DEVMOUNT's website:
http://devmount.de/Develop/Mozilo%20Plugins/FileInfo.html
