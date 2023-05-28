jQuery(function($) {

    window.selected = [];

    var updateSelectedTable = function() {
        var settings = {
            "url": "./output.json",
            "method": "GET",
            "timeout": 0,
        };
        $("#selectedContent").find('tbody').html("");
        $.ajax(settings).done(function (response) {
            //console.log(response);
            window.selected = response;
            refreshSelectedTable(response);
        });
    }
    var imgTag = function(src) {
        if (src) {
            return '<img src="'+src+'"/>';
        } else {
            return '<img/>';
        }
    }
    var refreshSelectedTable = function(streams) {
        $("#selectedContent").find('tbody').html("");
        //streams = response;
        for (let index = 0; index < streams.length; index++) {
            const stream = streams[index];
            row = $('<tr><td><span class="glyphicon glyphicon-menu-hamburger" style="color:#bbb; cursor:move;"></span></td><td>'+(index+1)+'</td><td>'+imgTag(stream.tvg_logo)+'</td><td class="name">'+stream.name+'</td><td class="url">'+stream.url+'</td><td>'+stream.group_title+'</td><td><button type="button" class="btn btn-sm btn-success"><i class="bi bi-link-45deg"></i></button></td><td><button type="button" class="btn btn-sm btn-info"><i class="bi bi-clipboard"></i></button></td><td><button type="button" class="btn btn-sm btn-danger">Remove</button></td></tr>');
            $("#selectedContent").find('tbody').append(row);
        }
        $('#outputDiv h3').html("Selected - "+streams.length+" streams");
        // initializes the row reordering for each row
        $("#selectedContent tbody tr").each(function () {
            initRowReordering($(this));
        });
        $("#selectedContent tbody tr button.btn-danger").click(function() {
            name = $(this.parentNode.parentNode).find('td.name').html();
            //stream = findStreamByName(window.selected, name);
            var filtered = window.selected.filter(function(el){ 
                return (el.name != name);
            });
            window.selected = filtered;
            refreshSelectedTable(filtered);
        });
        $("#selectedContent tbody tr button.btn-info").click(function(event) {
            console.log("that's it");
            url = $(this.parentNode.parentNode).find('td.url').html();
            copyToClipboard(url);
            event.stopPropagation();
        });
        $("#selectedContent tbody tr button.btn-success").click(function(event) {
            url = $(this.parentNode.parentNode).find('td.url').html();
            window.open(url, '_blank');
            event.stopPropagation();
        });
    }
    var link = function(url) {
        return "<a href='"+url+"' target=blank>&#127760;</a>";
    }
    var updateStreamTable = function(id) {
        $('body').addClass("wait");
        var settings = {
            "url": "./fetcher.php?id="+id+"&m=json",
            "method": "GET",
            "timeout": 0,
        };
        $("#sourceContent").find('tbody').html("");
        $.ajax(settings).done(function (response) {
            //console.log(response);
            streams = response;
            source = findSourceById(id);
            bad = [];
            if (source.remove && (source.remove.length > 0)) {
                bad = source.remove;
            }

            for (let index = 0; index < streams.length; index++) {
                const stream = streams[index];
                row = $('<tr></tr>');
                row.append($('<td>'+imgTag(stream.tvg_logo)+'</td><td class="name">'+stream.name+'</td><td class="url">'+stream.url+'</td><td><button type="button" class="btn btn-sm btn-info"><i class="bi bi-clipboard"></i></button></td>'));
                // check if opts
                if (stream.opts && (stream.opts.length > 0)) {
                    row.addClass("hasOptions");
                }
                // check if bad
                if (bad.includes(stream.name)) {
                    row.addClass("badRow");
                    row.append($('<td><button type="button" class="btn btn-sm btn-danger">Reset</button></td>'));
                } else {
                    row.append($('<td><button type="button" class="btn btn-sm btn-danger">Bad</button></td>'));
                }
                if (findStreamByURL(window.selected, stream.url) || bad.includes(stream.name)) {
                    //console.log(stream.name+" already in selected")
                    row.append($('<td style="text-align: center;">-</td>'));
                } else {
                    row.append($('<td><button type="button" class="btn btn-sm btn-success">Add</button></td>'));
                }
                $("#sourceContent").find('tbody').append(row);
            }
            $('#streamDiv h3').html(id+" - "+streams.length+" streams");

            // initializes the row reordering for each row
            $("#sourceContent tbody tr").each(function () {
                //initStreamActions($(this)); // why?
            });
            $("#sourceContent tbody tr button.btn-info").click(function() {
                url = $(this.parentNode.parentNode).find('td.url').html();
                copyToClipboard(url);
            });
            $("#sourceContent tbody tr button.btn-danger").click(function() {
                // flag as bad (add to remove array in config source array)
                // what stream?
                name = $(this.parentNode.parentNode).find('td.name').html()
                $(this).closest('tr').addClass("badRow");
                // what source?
                source = findSourceById(id);
                console.log(source);
                // update source
                if (!source.remove) {
                    source.remove = [];
                }
                source.remove.push(name);
                // save config
                console.log(window.config.sources);
                var settings = {
                    "url": "./save.php?m=config",
                    "method": "POST",
                    "timeout": 0,
                    "headers": {
                        "Content-Type": "application/json"
                    },
                    "data": JSON.stringify(window.config),
                };

                $.ajax(settings).done(function (response) {
                    console.log(response);
                    //location.reload();
                });
            });
            $("#sourceContent tbody tr button.btn-success").click(function() {
                name = $(this.parentNode.parentNode).find('td.name').html();
                stream = findStreamByName(streams, name);
                window.selected.push(stream);
                refreshSelectedTable(window.selected);
            });
            $('body').removeClass("wait");
        });   
    }
    var copyToClipboard = function (element) {
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val(element).select();
        document.execCommand("copy");
        $temp.remove();
    }
    var findSourceById = function(id) {
        for (let index = 0; index < window.config.sources.length; index++) {
            const element = window.config.sources[index];
            if (element.title == id) {
                return element;
            }
        }
        return null;
    }
    var removeSourceById = function(id) {
        newsources = [];
        for (let index = 0; index < window.config.sources.length; index++) {
            const element = window.config.sources[index];
            if (element.title != id) {
                newsources.push(element);
            }
        }
        window.config.sources = newsources;
    }
    var findStreamByName = function(streams, name) {
        for (let index = 0; index < streams.length; index++) {
            const element = streams[index];
            if (element.name == name) {
                return streams[index];
            }
        }
        return null;
    }
    var findStreamByURL = function(streams, url) {
        for (let index = 0; index < streams.length; index++) {
            const element = streams[index];
            if (element.url == url) {
                return streams[index];
            }
        }
        return null;
    }
    var decodeEntities = function(encodedString) {
        var textArea = document.createElement('textarea');
        textArea.innerHTML = encodedString;
        return textArea.value;
    }
    var onAfterReordering = function(table) {
        if (table == "selectedContent") {
            orderedSelected = [];
            $("#"+table+" tbody tr").each(function(index, row) {
                $($(row).find("td").get(1)).html((index + 1));
                stream = findStreamByName(window.selected, $($(row).find("td").get(3)).html());
                orderedSelected.push(stream);
            });
            // reorder window.selected too
            window.selected = orderedSelected;    
        } else if (table == "sourceTable") {
            $("#"+table+" tbody tr").each(function(index, row) {
                $($(row).find("td").get(0)).html((index + 1));
            });
        }
    };
    window.onFiltering = function(table, term) {
        $("#"+table+" tbody tr").each(function(index, row) {
            term = term.toLocaleLowerCase();
            name = "";
            url = "";
            if (table == "sourceContent") {
                name = $($(row).find("td").get(1)).html().toLocaleLowerCase();
                url = $($(row).find("td").get(2)).html().toLocaleLowerCase();
            } else if (table == "selectedContent") {
                name = $($(row).find("td").get(4)).html().toLocaleLowerCase();
                url = $($(row).find("td").get(5)).html().toLocaleLowerCase();
            } else {
                return;
            }
            if (!(name.includes(term) || url.includes(term)) && (term != '')) {
                $(row).hide();
            } else {
                $(row).show();
            }
        });
    };    
    var initStreamActions = function(row) {
        $(row).click(function (e) {
            console.log(e.target.parentNode.parentNode.parentElement.id);
        });
    }
    var initSourceClick = function(row) {
        $(row).click(function (e) {
            //console.log(e.target.parentNode.parentNode.parentElement.id);
            $(e.target.parentNode.parentNode.childNodes).removeClass("selectedRow");
            $(e.target.parentElement).addClass("selectedRow");
            source = findSourceById(e.target.parentElement.id);
            if (source.url) {
                $('#selectedSourceURL').html(source.url);
            }
            if (source.file) {
                $('#selectedSourceURL').html(source.file);
            }
            updateStreamTable(e.target.parentElement.id);
        });
    }
    var showOptions = function(array) {
        var opts = "";
        if (array) {
            for (let index = 0; index < array.length; index++) {
                const element = array[index];
                opts += element + "\n";
            }    
        }
        return opts;
    }
    var readOptions = function(textarea) {
        const arr = textarea.split('\n').filter(element => element);
        return arr;
    }
    var loadDetails = function(stream) {
        window.current = stream;
        $('#streamid').val(stream.tvg_id);
        $('#streamname').val(stream.name);
        $('#streamlogo').val(stream.tvg_logo);
        $('#streamlogourl').attr("src",stream.tvg_logo);
        $('#streamurl').val(stream.url);
        $('#streamgrp').val(stream.group_title);
        $('#streamopts').val(showOptions(stream.opts));
    }
    var initRowReordering = function(row) {
        $(row).css("cursor", "move");
        $(row).click(function (e) {
            //console.log(e.target.parentNode.parentNode.parentElement.id);
            $(e.target.parentNode.parentNode.childNodes).removeClass("selectedRow");
            $(e.target.parentElement).addClass("selectedRow");
            name = $(e.target.parentNode).find('td.name').html();
            console.log(findStreamByName(window.selected, name));
            loadDetails(findStreamByName(window.selected, name));
        });
        $(row).shieldDraggable({
            helper: function (params) {
                // the draggable helper is the element used as a preview of the element being dragged
                // it can be a copy, or the actual element

                // here we create a copy of the dragged row and add it in a table, 
                // so that the styles can be applied
                var helper = $('<table class="table table-hover"></table>');
                var tbody = $('<tbody />').appendTo(helper);
                tbody.append(row.clone());

                // fix the style of the TDs in the helper row - widths are copied from the original row
                // this will make the drag helper look like the original
                helper.find('td').each(function (index) {
                    $(this).width($(row.find('td')[index]).width());
                });
                helper.width(row.width());

                return helper;
            },
            events: {
                start: function (e) {
                    // add a custom class to the dragged element
                    // this will "hide" the row being dragged
                    $(row).addClass("dragged");
                },
                drag: function (e) {
                    // as the element is dragged, determine where to move the dragged row
                    var element = $(e.element),
                        elTopOffset = element.offset().top;

                    var rows = $(row).siblings('tr').not('.dragged').get();

                    for (var i = 0; i < rows.length; i++) {
                        if ($(rows[i]).offset().top > elTopOffset) {
                            $(row).insertBefore($(rows[i]));
                            break;
                        }

                        // if last and still not moved, check if we need to move after
                        if (i >= rows.length - 1) {
                            // move element to the last - after the current
                            $(row).insertAfter($(rows[i]));
                        }
                    }
                },
                stop: function (e) {
                    // dragging has stopped - remove the added classes
                    $(row).removeClass("dragged");

                    // cancel the event, so the original element is NOT moved 
                    // to the position of the handle being dragged
                    e.cancelled = true;
                    e.skipAnimation = true;

                    // call the on-after-reorder handler function right after this one finishes
                    setTimeout(function() {
                        onAfterReordering("selectedContent");
                    }, 50);
                }
            }
        });
    };

    // initializes the row reordering for each row
    $("#sourceTable tbody tr").each(function () {
        // initRowReordering($(this));
        initSourceClick($(this));
    });
    
    // TODO: implement "add back"
    // TODO: add file add mode (upload + reference)
    // or simpler version - add url per url in custom.m3u8
    // TODO: picture cache
    // TODO: tvg_language and tvg_country support
    // TODO: docker version
    // TODO: add check on copy URL <= ???
    // TODO: add open link - will work on ipad, even if not on mac
    // TODO: add "send to top" in selected section

    // clicking the Delete button should delete the row and resync ordering information
    $("#sourceTable tbody tr button.btn-danger").click(function(e) {
        e.preventDefault();
        console.log("removing "+$(this).closest('tr')[0].id);
        removeSourceById($(this).closest('tr')[0].id);
        $(this).closest('tr').remove();
        onAfterReordering("sourceTable");
        return false;
    }); 
    $("#mySaveSrcButton").click(function() {
        var settings = {
            "url": "./save.php?m=config",
            "method": "POST",
            "timeout": 0,
            "headers": {
                "Content-Type": "application/json"
            },
            "data": JSON.stringify(window.config),
        };

        $.ajax(settings).done(function (response) {
            console.log(response);
            location.reload();
        });
    });
    $("#mySaveButton").click(function() {
        //console.log(JSON.stringify(window.selected, null, 3));
        var settings = {
            "url": "./save.php?m=output",
            "method": "POST",
            "timeout": 0,
            "headers": {
                "Content-Type": "application/json"
            },
            "data": JSON.stringify(window.selected),
        };

        $.ajax(settings).done(function (response) {
            console.log(response);
        });
    });
    $("#myDetailsSaveButton").click(function() {
        //console.log(JSON.stringify(window.selected, null, 3));
        stream = findStreamByName(window.selected, window.current.name);
        stream.tvg_id = $('#streamid').val();
        stream.name = $('#streamname').val();
        stream.tvg_logo = $('#streamlogo').val();
        stream.url = $('#streamurl').val();
        stream.group_title = $('#streamgrp').val();
        if ($('#streamopts').val() != "") {
            stream.opts = readOptions($('#streamopts').val());
        } else if (stream.opts || (stream.opts.length == 0)) {
            delete stream.opts;
        }

        var settings = {
            "url": "./save.php?m=output",
            "method": "POST",
            "timeout": 0,
            "headers": {
                "Content-Type": "application/json"
            },
            "data": JSON.stringify(window.selected),
        };

        $.ajax(settings).done(function (response) {
            console.log(response);
            refreshSelectedTable(window.selected);
        });
    }); 
    $("#copyMainButton").click(function() {
        url = document.location.href.replace("admin.php", "output.php");
        copyToClipboard(url);
    });

    $('#exampleModal').on('show.bs.modal', function (event) {
        var modal = $(this)
        modal.find('.modal-footer button.btn-primary').click(function(e) {
            e.preventDefault();

            name = modal.find('#source-name').val();
            url = modal.find('#source-url').val();

            config_obj = {};
            config_obj.url = url;
            config_obj.title = name;

            window.config.sources.push(config_obj);

            modal.modal('toggle'); //or  $('#IDModal').modal('hide');
            //console.log(name+" / "+url);
            var row = $('<tr id="'+name+'">' + 
                '<td style="max-width: 20px"></td>' + 
                '<td>' + name + '</td>' + 
                '<td>...</td>' + 
                '</tr>').appendTo($("#sourceTable tbody"));
            initSourceClick(row);
            onAfterReordering("sourceTable");
            return false;
        });
    });
    updateSelectedTable();
});