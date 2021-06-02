<div id="aapvl_content" class="container-fluid">

    <?php
    $results = $this->results;

    $columns = array("URL", "Source");

    $table_columns = '{id: "URL", name:"URL", minWidth: 200, field:"URL",  formatter: url},{id: "Source", name:"Source", width: 200, field:"Source", formatter: url},';

    $additional_columns = $results[0]->manual_results;

    $additional_columns = process_json($additional_columns);

    $additional_columns = array_keys($additional_columns);

    $columns = array_merge($columns, $additional_columns);
 
    foreach ($additional_columns AS $column) {

        $table_columns = $table_columns . '{id: "' . $column . '", name:"' . $column . '", width: 120, field:"' . $column . '", editor: Slick.Editors.Text},';
    }

    $table_columns = rtrim($table_columns, ",");

    $data = "";
    
    
    foreach ($results AS $result) {
        
        $url = 'URL: "http://'.$result->url_resources.'"';
        $source = 'Source: "'.$result->path_resources.'"';
        $json_results = process_json($result->manual_results); 
        
        $table_results = "";
               
        foreach($additional_columns AS $additional_column) {
        $table_results = $table_results.$additional_column.': "'.$json_results[$additional_column].'", ';    
        }
        
         $table_results = rtrim($table_results, ", ");
        
        $data = $data.'{'.$url.', '.$source.', '.$table_results.'},';
              
    }
    
    $data = rtrim($data, ",");



    ?>

    <link rel="stylesheet" href="<?php echo URL; ?>inc/slickgrid/slick.grid.css" type="text/css"/>
    <link rel="stylesheet" href="<?php echo URL; ?>inc/slickgrid/css/smoothness/jquery-ui-1.11.3.custom.css" type="text/css"/>

    <script src="<?php echo URL; ?>inc/slickgrid/lib/firebugx.js"></script>

    <script src="<?php echo URL; ?>inc/slickgrid/lib/jquery-1.11.2.min.js"></script>
    <script src="<?php echo URL; ?>inc/slickgrid/lib/jquery-ui-1.11.3.min.js"></script>
    <script src="<?php echo URL; ?>inc/slickgrid/lib/jquery.event.drag-2.3.0.js"></script>
    <script src="<?php echo URL; ?>inc/slickgrid/slick.core.js"></script>
    <script src="<?php echo URL; ?>inc/slickgrid/plugins/slick.cellrangedecorator.js"></script>
    <script src="<?php echo URL; ?>inc/slickgrid/plugins/slick.cellrangeselector.js"></script>
    <script src="<?php echo URL; ?>inc/slickgrid/plugins/slick.cellselectionmodel.js"></script>
    <script src="<?php echo URL; ?>inc/slickgrid/slick.formatters.js"></script>
    <script src="<?php echo URL; ?>inc/slickgrid/slick.editors.js"></script>
    <script src="<?php echo URL; ?>inc/slickgrid/slick.grid.js"></script>

    <style>



        #container {
            font: 12px Helvetica, Arial, sans-serif;
          
        }




    </style>
    <div id="controls" style="margin-bottom: 5px;">
        <h4><?php echo $_SESSION["language_json"]["Manual_Judgement"] . $_SESSION["language_json"]["Show_Case"] . " " . $this->id_cases ?></h4>
        <button onclick="undo()">Rückgängig</button>
        <button onclick="save()">Speichern</button>
        <button onclick="export()">Exportieren</button></div>

  <div style="width:100%;">
    <div id="container" style="width:100%;height:500px;"  align="left"></div>

</div>
    <script>
        function requiredFieldValidator(value) {
            if (value == null || value == undefined || !value.length) {
                return {valid: false, msg: "This is a required field"};
            } else {
                return {valid: true, msg: null};
            }
        }


        function url(row, cell, value, columnDef, dataContext) {
            return "<a href='" + value + "' target='_blank'>" + value + "</a>";
        }
        

        var grid,
                data = [<?php echo $data; ?>]
                columns = [
<?php echo $table_columns; ?>],
                options = {
                    enableCellNavigation: true,
                    enableColumnReorder: false,
                    editable: true,
                    enableAddRow: false,
                    asyncEditorLoading: false,
                    autoEdit: false,
                    editCommandHandler: queueAndExecuteCommand
                };

        var commandQueue = [];

        function queueAndExecuteCommand(item, column, editCommand) {
            commandQueue.push(editCommand);
            editCommand.execute();
        }

        function undo() {
            var command = commandQueue.pop();
            if (command && Slick.GlobalEditorLock.cancelCurrentEdit()) {
                command.undo();
                grid.gotoCell(command.row, command.cell, false);
            }
        }
        
        
        
      
        grid = new Slick.Grid("#container", data, columns, options);

        grid.setSelectionModel(new Slick.CellSelectionModel());

    </script> 

