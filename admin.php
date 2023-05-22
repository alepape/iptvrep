<?php

require_once("common.php");

// load config file
$configfile = __DIR__ .'/config.json';
$configjson = file_get_contents($configfile);
$configdata = json_decode($configjson, true);

?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width">
  <title>IPTV sources manager</title>
  
  <!-- jQuery and Shield UI includes -->
  <link id="themecss" rel="stylesheet" type="text/css" href="https://www.shieldui.com/shared/components/latest/css/light/all.min.css" />
  <script type="text/javascript" src="https://www.shieldui.com/shared/components/latest/js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="https://www.shieldui.com/shared/components/latest/js/shieldui-all.min.js"></script>
  
  <!-- Bootstrap includes -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.2/font/bootstrap-icons.css">
  
  <link rel="stylesheet" href="./style.css">
  <script>
        window.config = JSON.parse('<?=json_encode($configdata)?>');
  </script>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div id="sourceDiv" class="col-md-4">
                <h3 style="float: left;">Sources</h3>
                <div style="float: right; margin-top: 20px">
                    <button id="myAddButton" type="button" class="btn btn-info" data-toggle="modal" data-target="#exampleModal">Add Source</button>
                    <button id="mySaveSrcButton" type="button" class="btn btn-success">Save</button>
                </div>
                <table class="table table-hover" id="sourceTable">
                    <thead>
                        <th>Order</th>
                        <th>Title</th>
                        <th>&nbsp;</th>
                    </thead>
                    <tbody>
                        <?php
                            $pos = 1;
                            foreach ($configdata['sources'] as $source) {
                                echo '<tr id="'.$source['title'].'">';
                                echo '<td>'.$pos++.'</td>';
                                echo '<td>'.$source['title'].'</td>';
                                echo '<td><button type="button" class="btn btn-sm btn-danger">Delete</button></td>';
                                echo "</tr>";
                            }
                        ?>
                    </tbody>
                </table>
            </div>

            <div id="streamDiv" class="col-md-8">
                <h3 style="float: left;">Select a source</h3>
                <div style="float: right; margin-top: 20px">
                <!-- <button id="myRefreshButton" type="button" class="btn btn-success">Refresh</button> -->
                <input class="form-control" type="text" placeholder="Filter streams" id="sourceFilter" onkeyup="window.onFiltering('sourceContent', this.value);"></input>
                </div>
                <div style="clear: both;">
                    <b>Source:</b> <span id="selectedSourceURL">&nbsp;</span>
                    <br/>
                </div>
                <div id="streamDivTable">
                <table class="table table-hover" id="sourceContent">
                    <thead>
                        <th>&nbsp;</th>
                        <th>Name</th>
                        <th>URL</th>
                        <th>&nbsp;</th>
                        <th>&nbsp;</th>
                        <th>&nbsp;</th>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
        <div class="row">
            <div id="outputDiv" class="col-md-8">
                <h3 style="float: left;">Selected</h3>
                <div style="float: right; margin-top: 20px; margin-bottom: 20px" class="input-group">
                    <input class="form-control" type="text" placeholder="Filter streams" id="selectedFilter" onkeyup="window.onFiltering('selectedContent', this.value);"></input>
                    <div class="input-group-btn">
                        <button id="copyMainButton" type="button" class="btn btn-info"><i class="bi bi-clipboard"></i></button>
                        <button id="mySaveButton" type="button" class="btn btn-success">Save</button>
                    </div>
                </div>
                <div id="outputDivTable">
                    <table class="table table-hover" id="selectedContent">
                        <thead>
                            <th>&nbsp;</th>
                            <th>Order</th>
                            <th>&nbsp;</th>
                            <th>Name</th>
                            <th>URL</th>
                            <th>Group</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="detailsDiv" class="col-md-4">
                <h3 style="float: left;">Details</h3>
                <div style="margin-top: 146px">
                    <form>
                    <div class="form-group">
                        <label for="streamid" class="col-form-label">ID:</label>
                        <input type="text" class="form-control" id="streamid">
                    </div>
                    <div class="form-group">
                        <label for="streamname" class="col-form-label">Name:</label>
                        <input type="text" class="form-control" id="streamname">
                    </div>
                    <div class="form-group">
                        <label for="streamlogo" class="col-form-label">Logo:</label>
                        <input type="text" class="form-control" id="streamlogo">
                        <img id="streamlogourl" src=""/>
                    </div>
                    <div class="form-group">
                        <label for="streamurl" class="col-form-label">URL:</label>
                        <input type="text" class="form-control" id="streamurl">
                    </div>
                    <div class="form-group">
                        <label for="streamgrp" class="col-form-label">Group:</label>
                        <input type="text" class="form-control" id="streamgrp">
                    </div>
                    <div class="form-group">
                        <label for="streamopts" class="col-form-label">Options:</label>
                        <textarea rows="3" class="form-control" id="streamopts"></textarea>
                    </div>
                    </form>
                </div>
                <div style="float: right; margin-top: 20px">
                    <button id="myDetailsSaveButton" type="button" class="btn btn-success">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">New Source</h5>
        </div>
        <div class="modal-body">
            <form>
            <div class="form-group">
                <label for="source-name" class="col-form-label">Name:</label>
                <input type="text" class="form-control" id="source-name">
            </div>
            <div class="form-group">
                <label for="source-url" class="col-form-label">URL:</label>
                <input type="text" class="form-control" id="source-url">
            </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" type="submit" name="submit" value="Submit">Add</button>
        </div>
        </div>
    </div>
    </div>

<script src="./admin.js"></script>

</body>
</html>