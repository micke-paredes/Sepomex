<?php
ini_set("memory_limit", "4G");
require('vendor/box/spout/src/Spout/Autoloader/autoload.php');
include_once('database.php');

use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;

class Sepomex {

    private ?\Box\Spout\Reader\ReaderInterface $reader = null;
    private $link = null;
    private array $cells = [];

    public function __construct() {
        try {
            $this->reader = ReaderEntityFactory::createReaderFromFile('/home/polaroid/Downloads/sepomex/Sepomex.ods');
            $this->reader->open('/home/polaroid/Downloads/sepomex/Sepomex.ods');
            $this->link = new Database();
        } catch (\Box\Spout\Common\Exception\IOException $e) {
            echo "Error to load file";
        }
    }

    public function createCountries() {
        try {
            $this->link->exec("DROP TABLE IF EXISTS catalog_countries");
            $this->link->exec("
                create table catalog_countries
                (
                    id               int auto_increment primary key,
                    name             varchar(255) not null,
                    id_user_register int          null,
                    id_user_delete   int          null,
                    is_active        tinyint(1)   null,
                    createdAt        datetime     not null,
                    updatedAt        datetime     not null
                )
            ");
            foreach ($this->reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $key => $row) {
                    if ($sheet->getName() === 'Pais') {
                        $this->cells = $row->getCells();
                        if ($key == 1) {
                            continue;
                        } else {
                            $countryExist = $this->link->getOnlyRow("
                                SELECT COUNT(name) AS exist 
                                FROM catalog_countries 
                                WHERE name = '" . $this->cells[0] . "'
                            ");
                            if ($countryExist['exist'] == 0) {
                                $register = $this->link->exec("
                                    INSERT INTO catalog_countries (name, id_user_register, is_active, createdAt, updatedAt) 
                                    VALUES ('" . $this->cells[0] . "', 1, true, now(), now())");
                            }
                        }
                    }
                }
            }
        } catch (\Box\Spout\Reader\Exception\ReaderNotOpenedException $e) {
            echo "Error to read file";
        }
    }

    public function createStates() {
        try {
            $this->link->exec("DROP TABLE IF EXISTS catalog_states");
            $this->link->exec("
                create table catalog_states
                (
                    id               int auto_increment primary key,
                    name             varchar(255) not null,
                    id_country       int          not null,
                    id_user_register int          null,
                    id_user_delete   int          null,
                    is_active        tinyint(1)   null,
                    createdAt        datetime     not null,
                    updatedAt        datetime     not null
                )
            ");
            foreach ($this->reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $key => $row) {
                    if ($sheet->getName() === 'Estados') {
                        $this->cells = $row->getCells();
                        if ($key == 1) {
                            continue;
                        } else {
                            $stateExists = $this->link->getOnlyRow("
                                SELECT COUNT(name) AS exist 
                                FROM catalog_states 
                                WHERE name = '" . $this->cells[1] . "'"
                            );
                            if ($stateExists['exist'] == 0) {
                                $register = $this->link->exec("
                                    INSERT INTO catalog_states (id_country, name, id_user_register, is_active, createdAt, updatedAt) 
                                    VALUES ('" . $this->cells[0] . "', '" . $this->cells[1] . "', 1, true, now(), now())"
                                );
                            }
                        }
                    }
                }
            }
        } catch (\Box\Spout\Reader\Exception\ReaderNotOpenedException $e) {
            echo "Error to read file";
        }
    }

    public function createCities() {
        try {
            $this->link->exec("DROP TABLE IF EXISTS catalog_cities");
            $this->link->exec("
                create table catalog_cities
                (
                    id               int auto_increment primary key,
                    name             varchar(255) not null,
                    id_state         int          not null,
                    id_user_register int          null,
                    id_user_delete   int          null,
                    is_active        tinyint(1)   null,
                    createdAt        datetime     not null,
                    updatedAt        datetime     not null
                )
            ");
            foreach ($this->reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $key => $row) {
                    if ($sheet->getName() !== 'Estados' && $sheet->getName() !== 'Pais') {
                        $this->cells = $row->getCells();
                        if ($key == 1) {
                            continue;
                        } else {
                            $state = $this->cells[2];
                            $city = $this->cells[3];

                            if ($city == '') { 
                                $city = "Sin Ciudad";
                            } 

                            $cityExists = $this->link->getOnlyRow("
                                SELECT COUNT(id) AS exist
                                FROM catalog_cities
                                WHERE name = '" . $city . "'
                                AND id_state = (
                                        SELECT id 
                                        FROM catalog_states 
                                        WHERE name = '". $state ."'
                                    )
                            ");

                            if ($cityExists['exist'] == 0) {
                                $stateId =  $this->link->getOnlyRow("
                                    SELECT id AS idState
                                    FROM catalog_states
                                    WHERE name = '" . $state . "'
                                ");

                                $register = $this->link->exec("
                                    INSERT INTO catalog_cities (id_state, name, id_user_register, is_active, createdAt, updatedAt)
                                    VALUES ('" . $stateId['idState'] . "', '" . $city . "', 1, true, now(), now())"
                                );
                            }
                        }
                    }
                }
            }
        } catch (\Box\Spout\Reader\Exception\ReaderNotOpenedException $e) {
            echo "Error to read file";
        }
    }

    public function createMunicipalities() {
        try {
            $this->link->exec("DROP TABLE IF EXISTS catalog_municipalities");
            $this->link->exec("
                create table catalog_municipalities
                (
                    id               int auto_increment primary key,
                    name             varchar(255) not null,
                    id_city          int          not null,
                    id_user_register int          null,
                    id_user_delete   int          null,
                    is_active        tinyint(1)   null,
                    createdAt        datetime     not null,
                    updatedAt        datetime     not null
                )
            ");
            foreach ($this->reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $key => $row) {
                    if ($sheet->getName() !== 'Pais' && $sheet->getName() !== 'Estados') {
                        $this->cells = $row->getCells();
                        if ($key == 1) {
                            continue;
                        } else {
                            $state = $this->cells[2];
                            $city = $this->cells[3];
                            $municipality = $this->cells[1];
                        
                            if ($city == '') {
                                $city = "Sin Ciudad";
                            }

                            $stateId = $this->link->getOnlyRow("SELECT id FROM catalog_states WHERE name = '". $state ."'");
                            $cityId = $this->link->getOnlyRow("SELECT id FROM catalog_cities WHERE name = '". $city ."' AND id_state = '" . $stateId['id'] . "' ");

                            $municipalityExists = $this->link->getOnlyRow("
                                SELECT COUNT(id) AS exist
                                FROM catalog_municipalities
                                WHERE name = '" . $municipality . "'
                                AND id_city = '" . $cityId['id'] . "'
                            ");

                            if ($municipalityExists['exist'] == 0) {
                                $register = $this->link->exec("
                                    INSERT INTO catalog_municipalities (name, id_city, id_user_register,  is_active, createdAt, updatedAt)
                                    VALUES ('" . $municipality . "', '" . $cityId['id'] . "', 1, true, now(), now())
                                ");
                            }
                        }
                    }
                }
            }
        } catch (\Box\Spout\Reader\Exception\ReaderNotOpenedException $e) {
            echo "Error to read file";
        }
    }

    public function createColonies() {
        try {
            $this->link->exec("DROP TABLE IF EXISTS catalog_colonies");
            $this->link->exec("
                create table catalog_colonies
                (
                    id               int auto_increment primary key,
                    name             varchar(255) not null,
                    id_municipality  int          not null,
                    zip_code         int          not null,
                    id_user_register int          null,
                    id_user_delete   int          null,
                    is_active        tinyint(1)   null,
                    createdAt        datetime     not null,
                    updatedAt        datetime     not null
                )
            ");
            foreach ($this->reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $key => $row) {
                    if ($sheet->getName() !== 'Pais' && $sheet->getName() !== 'Estados') {
                        $this->cells = $row->getCells();
                        if ($key == 1) {
                            continue;
                        } else {
                            $colony = $this->cells[0];
                            $municipality = $this->cells[1];
                            $state = $this->cells[2];
                            $city = $this->cells[3];
                            $zip_code = $this->cells[4];
                        
                            if ($city == '') {
                                $city = "Sin Ciudad";
                            }

                            $stateId = $this->link->getOnlyRow("SELECT id FROM catalog_states WHERE name = '". $state ."'");
                            $cityId = $this->link->getOnlyRow("SELECT id FROM catalog_cities WHERE name = '". $city ."' AND id_state = '" . $stateId['id'] . "' ");
                            $municipalityId = $this->link->getOnlyRow("SELECT id FROM catalog_municipalities WHERE name = '". $municipality ."' AND id_city = '" . $cityId['id'] . "' ");

                            $colonyExists = $this->link->getOnlyRow("
                                SELECT COUNT(name) AS exist
                                FROM catalog_colonies
                                WHERE name = '" . $colony . "' 
                                AND id_municipality = '" . $municipalityId['id'] . "'
                                AND zip_code = '" . $zip_code . "'"
                            );

                            if ($colonyExists['exist'] == 0) {
                                $register = $this->link->exec("
                                    INSERT INTO catalog_colonies (id_municipality, name, zip_code, id_user_register, is_active, createdAt, updatedAt)
                                    VALUES ('" . $municipalityId['id'] . "', '" . $colony . "', '" . $zip_code . "', 1, true, now(), now())"
                                );
                            }
                        }
                    }
                }
            }
        } catch (\Box\Spout\Reader\Exception\ReaderNotOpenedException $e) {
            echo "Error to read file";
        }
    }



    public function __destruct() {
        $this->reader->close();
        $this->link = null;
        $this->cells = [];
    }
}

$sepomex = new Sepomex();
$sepomex->createCountries();
$sepomex->createStates();
$sepomex->createCities();
$sepomex->createMunicipalities();
$sepomex->createColonies();
