# digiYiiKam
Web frontend for the famous open-source software DigiKam Photo Management

# Requirements
| Component    | Software    |
| --- | --- |
| OS  | Linux or MAC with PHP support |
| Database | MySQL or MariaDB |

digiYiiKam uses the Yii2-Framework and was tested with DigiKam running with MySQL. 

## SQLite support 
SQLite should also work, but is not tested. digiYiiKam must have access to the database file from DigiKam.

## Read access to photo collection(s)
Be sure, that the Apache or web server user (e.g. www-data on Debian, Ubuntu, ...) has read-access to the image files.

# Installation

## PHP components

```
sudo apt update
sudo apt install php php-pdo php-intl php-xml php-zip php-mbstring php-mysql php-sqlite3 unzip composer php-apcu
```

## Application
Go to the folder, where digiYiiKam shall be installed. E.g. `cd /var/www/`

We will now install the Yii2 components and needed extensions with composer.

```
composer create-project --prefer-dist yiisoft/yii2-app-basic digiyiikam

cd digiyiikam

composer require --prefer-dist daxslab/yii2-thumbnailer "*"
composer require 2amigos/yii2-gallery-widget
composer require kartik-v/yii2-widget-sidenav "*"
composer require yidas/yii2-fontawesome
composer require onmotion/yii2-widget-apexcharts
composer require dmstr/yii2-ajax-button
composer require kartik-v/yii2-widget-spinner "@dev"


cd /tmp
git clone https://github.com/patschwork/digiYiiKam.git

# Copy/move (with overwrite) everything from /tmp/digiYiiKam into /var/www/digiyiikam

# run database migrations
./yii migrate
```

# Configuration
Enter your credentials for the DigiKam database: `/var/www/digiyiikam/config/db_digikam.php`

Create a new (MySQL) database named digiyiikam aside the digikam database

```sql
CREATE DATABASE `digiYiiKam` /*!40100 DEFAULT CHARACTER SET latin1 */
```

Enter your credentials for the digiYiiKam database: `/var/www/digiyiikam/config/db.php`

Set a cookieValidationKey in: `/var/www/digiyiikam/config/web.php`

Add the collection paths to your local pictures folder. This may be different than from the settings in DigiKam when they are different machines. 
`/var/www/digiyiikam/config/params.php`

# Get started
## Prepare thumbnails table
Go to the digiyiikam folder
`php yii utils/init-thumbnails-database`

## Build thumbnails and raw-previews
Go to the digiyiikam folder
`php yii utils/generate-thumbnails`

# New or updated data in digiKam
When you add photos to your collections digiYiiKam needs to know about this. 
You can also create regular schedules (e.g. with a cron job) to automate this. 
Note: Tags and other metadata are always realtime taken from digiKam. 

## Refresh
```
php yii utils/add-thumbnails-database
php yii utils/generate-thumbnails
```


# Links
https://www.digikam.org

https://docs.kde.org/trunk5/en/digikam-doc/digikam/using-setup.html

https://www.yiiframework.com/doc/guide/2.0/en/start-installation

# Some useful information
- digiYiiKam needs it's own database to avoid changes to the digiKam database.
- The digiKam database will only the read, with one exception by setting the "heart" tag in digiYiiKam which will make use of the tags also used by digiKam
- digiYiiKam will produce new thumbnails and can't reuse those from digiKam (digiKam thumbnails are incompatible to be shown in the webbrowser). The new thumbnails are stored in the digiYiiKam database
- JPEG images are shown directly from the source collection folder
- RAW images (e.g. CR2, CR3, DNG, NEF) must be converted as JPEG and are store in the digiYiiKam database
- digiYiiKam has command line tools for building the thumbnails and converting RAW images to JPEG
- The orignial files will never be modified!
