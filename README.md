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
    {FileInfo|<file>|<template>|<linktext>}
Inserts the file download link and file information elements.

1. Parameter ```<file>```: The filename like @=Category:Example.zip=@.
2. Parameter ```<template>```: The arrangement of the elements. Possible placeholder are: ```#LINK#```, ```#TYPE#```, ```#SIZE#```, ```#DATE#``` and ```#COUNT#```.
3. Parameter ```<linktext>```: Optional, text for download link. If this parameter is not set, filename is used.

#### Example:
    {FileInfo|@=Category:Example.zip=@|#LINK# is a #TYPE# file, has size #SIZE# and was downloaded #COUNT# times.|This file}

## License
This Plugin is distributed under *GNU General Public License, Version 3* (see LICENSE) or, at your choice, any further version.

## Documentation
A detailed documentation and demo can be found here:  
https://github.com/devmount-mozilo/FileInfo/wiki/Dokumentation [german]

---

This project is completely free to use. If you enjoy it and don't have the time to contribute, please consider [donating via Paypal](https://paypal.me/devmount) or [sponsoring me](https://github.com/sponsors/devmount) to support further support and development. :green_heart:
