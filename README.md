# Sepomex
This project create a tables to export data of correos de mexico service, extracting data from the .ods file, you need composer and php 7.4 or higher and MySQL 7 or higher

## Install
Execute composer install (probably you need install some extension that you not have), then execute in a command line or terminal the next command: 

```sh
    php readerSepomex.php
```

Please ignore the warnings show in the shell, no have effect on the sql insert. If the proccess is complete successfully execute the next Query on your SQL Editor:

```sh
    SELECT
        country.name AS Country,
        state.name AS State,
        city.name AS City,
        municipality.name AS Municipality,
        colony.name AS Colony,
        colony.zip_code AS ZipCode
    FROM catalog_states AS state
    INNER JOIN catalog_countries AS country ON country.id = state.id_country
    INNER JOIN catalog_cities AS city ON city.id_state = state.id
    INNER JOIN catalog_municipalities AS municipality ON municipality.id_city = city.id
    INNER JOIN catalog_colonies AS colony ON colony.id_municipality = municipality.id
    WHERE colony.zip_code = YOUR_ZIP_CODE
    AND colony.is_active = true;
```
And enjoy!
