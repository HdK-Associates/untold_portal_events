<?php 
class HdkSpektrixCli{
    
    public function import_events() {
        $importer = new HdKSpPopulate;
        $importer->UpdateTables();
	}
    /**
     * Import Single Event
     *
     * <id>
     * : The Spektrix ID of event to import
     * @param [type] $args
     * @return void
     */
    public function update_event($id){
        $importer = new HdKSpPopulate;
        $event = $importer->UpdateSingleEvent(null, $id[0]);
        $importer->UpdateTablesEventInstance($event[1],0,true);
    }

    public function import_event($id){
        $importer = new HdKSpPopulate;
        $event = $importer->ImportEvent(null, $id[0]);
        if(is_array($event)) {
            $importer->UpdateTablesEventInstance($event[1],0,true);
        }
    }

    public function rebuild_wp_data(){
        $importer = new HdKSpPopulate;
        $importer->UpdatePosts();
    }
}