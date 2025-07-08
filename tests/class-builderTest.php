<?php
use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{
    public function testCreateTables()
    {
       global $wpdb;
        $HdKSpInit = new HdKSpBuild;
        $HdKSpInit->createTables();
        $table = $wpdb->get_results("SHOW TABLES LIKE 'wp_spektrix_events'");
        $this->assertNotEmpty($table);
    }

    public function testCreateTablesIfNoTablesExist()
    {
        global $wpdb;
        $HdKSpInit = new HdKSpBuild;
        $HdKSpInit->DropTables();
        $HdKSpInit->createTables();
        $table = $wpdb->get_results("SHOW TABLES LIKE 'wp_spektrix_events'");
        $this->assertNotEmpty($table);
        $HdKSpInit->RestoreBackupTables();
    }

    public function testCreateTempTables(){
        global $wpdb;
        $HdKSpInit = new HdKSpBuild;
        $HdKSpInit->createTempTables();
        $table = $wpdb->get_results("SHOW TABLES LIKE 'wp_spektrix_events_temp'");
        $this->assertNotEmpty($table);
        $HdKSpInit->DropTempTables();
    }

    public function testDropTables(){
        global $wpdb;
        $HdKSpInit = new HdKSpBuild;
        $HdKSpInit->createTables();
        $HdKSpInit->DropTables();
        //Checking Tables were dropped
        $table = $wpdb->get_results("SHOW TABLES LIKE 'wp_spektrix_events'");
        $this->assertEmpty($table);
        //Checking Backup Tables were created
        $table = $wpdb->get_results("SHOW TABLES LIKE 'wp_spektrix_events_backup'");
        $this->assertNotEmpty($table);
    }

    public function testDropTempTables(){
        global $wpdb;
        $HdKSpInit = new HdKSpBuild;
        $HdKSpInit->createTempTables();
        $HdKSpInit->DropTempTables();
        $table = $wpdb->get_results("SHOW TABLES LIKE 'wp_spektrix_events_temp'");
        $this->assertEmpty($table);
    }
}