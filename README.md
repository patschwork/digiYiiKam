# digiYiiKam
Web frontend for the famous open-source software DigiKam Photo Management

# Requirements
OS: Linux or MAC with PHP
Database: MySQL or MariaDB

digiYiiKam uses the Yii2-Framework and was tested with DigiKam running with MySQL. 

## SQLite support 
SQLite should also work, but is not tested. digiYiiKam must have access to the database file from DigiKam.

## Read access to photo collection(s)
Be sure, that the Apache or web server user (e.g. www-data on Debian, Ubuntu, ...) has read-access to the image files.

# Installation

## Additional components for PHP

```
sudo apt install composer
sudo apt install php-apcu
```

## Application
Go to the folder, where digiYiiKam shall be installed. E.g. `cd /var/www/`

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

Add the collection paths to your local pictures folder. This may be different that from the setting in DigiKam when this are different machines. 
`/var/www/digiyiikam/config/params.php`

