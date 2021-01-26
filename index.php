<html>

<head>

<title>
<?php
$cwd = explode("/",getcwd());
$folder = array_pop($cwd);
echo $folder;
?>
</title>

<!-- <script type="text/javascript" src="http://livejs.com/live.js"></script> -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script defer src="//code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<script defer src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/jstree.min.js"></script>
<script defer src="https://cdnjs.cloudflare.com/ajax/libs/mark.js/8.11.1/jquery.mark.min.js"></script>
<link defer rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/themes/default/style.min.css" />
<link defer rel="stylesheet" href="https://unpkg.com/spectre.css/dist/spectre.min.css">
<link defer rel="stylesheet" href="https://unpkg.com/spectre.css/dist/spectre-exp.min.css">
<link defer rel="stylesheet" href="https://unpkg.com/spectre.css/dist/spectre-icons.min.css">
<link rel="icon" type="image/png" href="../trashcan.png" />

<style>

mark {
    padding: 0px;
    color: #fff;
    background: #4543dc;
}

#metadatacontainer {
    position: fixed;
    bottom: 0;
    width: 97%;
    padding: 10px; /* space between image and border */
}
#metadata {
    border:0.1rem solid #999;
    border-radius: 8px;
    background-color: rgba(255, 255, 255, .96);
    padding: 10px; /* space between image and border */
}

body {
    font-family: sans-serif;
    background-color: #fff;
}
body.dark-mode {
    background-color: #000;
}

.noborder {
    border: none;
    padding: 0px;
    /* to take up full width without showing horizontal scrollbar */
    width:90vw;
}

fieldset {
    border:0.1rem solid #999;
    border-radius: 8px;
    margin: 1px;
}
fieldset.dark-mode {
    border-color: #fff;
}

#custom-handle {
    width: 3em;
    font-family: sans-serif;
    text-align: center;
}

#slider {
    float:right;
    width: 10%;
}


.box {
    float:left;
    padding: 3px; /* space between image and border */
}

.plot {
    color: #070;
    text-decoration: none;
    border-bottom: 2px solid #bdb;
}

#images {
    position:relative;
    padding-top: 10px;
}

#container {
    margin: 1%;
}

.innerimg {
    padding: 3px;
}
.innerimg.dark-mode {
    filter: hue-rotate(180deg) invert(1);
    -webkit-filter: hue-rotate(180deg) invert(1);
}
.innerimg.super-saturate {
    filter: saturate(2.5);
    -webkit-filter: saturate(2.5);
}
.innerimg.dark-mode-super-saturate {
    filter: hue-rotate(180deg) invert(1) saturate(2.5);
    -webkit-filter: hue-rotate(180deg) invert(1) saturate(2.5);
}


legend {
    font-weight: bold;
    font-size: 90%;
    margin: 0px;
}
legend.dark-mode {
    color: #fff;
}

a {
}
a.dark-mode {
    color: #55f;
}


</style>

<?php

// get flat list with parent references
$data = array();
function fillArrayWithFileNodes( DirectoryIterator $dir , $theParent="#") {
    global $data;
    foreach ( $dir as $node ) {
        if (strpos($node->getFilename(), '.php') !== false) continue;
        if( $node->isDot() ) continue;
        if ( $node->isDir()) fillArrayWithFileNodes( new DirectoryIterator( $node->getPathname() ), $node->getPathname() );

        $tmp = array(
            "id" => $node->getPathname(),
            "parent" => $theParent,
            "text" => $node->getFilename(),
        );
        if ($node->isFile()) $tmp["icon"] = "file"; // can be path to icon file
        $data[] = $tmp;
    }
}
fillArrayWithFileNodes( new DirectoryIterator( '.' ) );

// get all files in flat list
$iter = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator('.', RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST,
    RecursiveIteratorIterator::CATCH_GET_CHILD
);
$paths = array('.');
foreach ($iter as $path => $dir) $paths[] = $path;

// get number of directories
$num_directories = 0;
foreach ( (new DirectoryIterator('.')) as $node ) {
    if( $node->isDot() ) continue;
    if ( $node->isDir()) $num_directories += 1;
}
?>

<script type="text/javascript">

function contains_any(str, substrings) {
    for (var i = 0; i != substrings.length; i++) {
        var substring = substrings[i];
        if (str.indexOf(substring) != - 1) {
            return substring;
        }
    }
    return null; 
}

function draw_objects(file_objects) {
    var jsrootbase = "http://uaf-8.t2.ucsd.edu/~namin/dump/jsroot/index.htm?json=../../.."+window.location.pathname;
    $("#images").html("");
    for (var ifo = 0; ifo < file_objects.length; ifo++) {
        var fo = file_objects[ifo];
        var name_noext = fo["name_noext"];
        var name = fo["name"];
        var path = fo["path"];
        var color = fo["color"];
        var pdf = fo["pdf"] || fo["name"];
        if (path) pdf = path+pdf;
        var txt_str = (fo["txt"].length > 0) ? `<span class='label label-rounded label-secondary'><a href='${fo["txt"]}' id='text_${fo["name_noext"]}'>text</a></span>` : "";
        var extra_str = (fo["extra"].length > 0) ? `<span class='label label-rounded'><a href='${fo["extra"]}' id='extra_${fo["name_noext"]}'>extra</a></span>` : "";
        var json_str = (fo["extra"].length > 0) ? `<span class='label label-rounded'><a href='${jsrootbase+fo["json"]}' id='json_${fo["name_noext"]}'>js</a></span>` : "";
        $("#images").append(`<div class='box' id='${name_noext}'>
                    <fieldset class='has-dark'>
                        <legend class='has-dark'>
                            <span class='label label-rounded'>${name_noext}</span>
                            ${txt_str+extra_str+json_str}
                        </legend>
                        <a href='${pdf}'>
                            <img class='innerimg has-dark' name='${name_noext}' src='${path+"/"+name}' height='300px' />
                        </a>
                    </fieldset>
                </div>`);
    }
}

function draw_filtered(filter_paths) {
    var temp_filelist = filelist.filter(function(value) {
            return contains_any(value, filter_paths);
            });
    var temp_objects = make_objects(temp_filelist);
    draw_objects(temp_objects);
}

function make_objects(filelist) {
    var file_objects = [];
    for (var i = 0; i < filelist.length; i++) {
        var f = filelist[i];
        var ext = f.split('.').pop();
        // if (!in_array(ext, ["svg", "gif", "png", "jpeg", "jpg"])) continue; // NOT in older versions of php?
        if ((ext != "png") && (ext != "svg") && (ext != "gif") && (ext != "jpeg") && (ext != "jpg")) continue;
        var color = "";
        var name = f.split('/').reverse()[0];
        var path = f.replace(name, "");
        var name_noext = name.replace("."+ext,"");
        var pdf = (filelist.indexOf(path+name_noext + ".pdf") != -1) ? name_noext+".pdf" : "";
        var txt = (filelist.indexOf(path+name_noext + ".txt") != -1) ? name_noext+".txt" : "";
        var extra = (filelist.indexOf(path+name_noext + ".extra") != -1) ? name_noext+".extra" : "";
        var json = (filelist.indexOf(path+name_noext + ".json") != -1) ? name_noext+".json" : "";
        file_objects.push({
                "path": path,
                "name_noext": name_noext,
                "name":name,
                "ext": ext,
                "pdf": pdf,
                "txt": txt,
                "extra": extra,
                "json": json,
                "color": color,
                });
    }
    // sort by name
    file_objects.sort(function(a,b) { return a["name"] > b["name"]; });
    return file_objects;
}

function register_hover() {
    console.log("registering hover");
    $("[id^=text_],[id^=extra_]").hover(
        function() {
            console.log("fading in hover");
            $(this).delay(500).queue(function(){
                $(this).addClass('hovered').siblings().removeClass('hovered');
                var link = $(this).attr('href');
                console.log(link);
                $("#metadata").load(link, function() { });
                console.log("fading in");
                $("#metadata").fadeIn();
            });
        },function() {
            $(this).finish();
            $("#metadata").delay(500).fadeOut();
        } 
    );
}

function register_description_hover() {
    console.log("registering hover");
    console.log($("[class^=plot]"));
    $("[class^=plot]").hover(
        function() {
            console.log("fading in hover");
            var plotname = $(this).text();
            var plotselector = "#" + plotname;
            console.log(plotname);
            console.log($(plotselector));
            $(plotselector).effect('highlight',{"color":"9d9"},500);
            $(plotselector).finish();
        },function() {
        } 
    );
}

function add_links_to_description(objects) {
    console.log(objects);
    var desc_src = $("#description").html();
    console.log(desc_src);
    for (var i = 0; i < objects.length; i++) {
        var plotname = objects[i]["name_noext"];
        desc_src = desc_src.split(plotname).join("<a href=\"#"+plotname+"\" class=\"plot\">"+plotname+"</a>");
    }
    console.log(desc_src);
    $("#description").html(desc_src);
}

// ultimately this will be a master filelist with all files recursively in this directory
// then we will filter for files we want to show
var obj = <?php echo json_encode($data); ?>;
var filelist = <?php echo json_encode($paths); ?>;


$(function() {

    if (<?php echo $num_directories ?> > 0) {
        $('#jstree_demo_div')
            .on('changed.jstree', function(e,data) {
                draw_filtered(data.selected);
            })
            .jstree( {
                "core": {
                    'multiple': true,
                    'themes' : {
                       'stripes' : true
                    },
                    "data": 
                        obj
                }
            }); 
    }

    var markre = function(pattern) {
        
        var context=$("legend");
        $("#message").html("");
        $("#message").removeClass("label");
        context.unmark();

        var modifier = "";
        if (pattern.toLowerCase() == pattern) modifier = "i"; // like :set smartcase in vim (case-sensitive if there's an uppercase char)

        $(".form-icon").addClass("loading");

        var regex = new RegExp(pattern,modifier);
        context.markRegExp(regex,{
            done: function(counter) {
                console.log(counter);
                $(".form-icon").removeClass("loading");
                if (counter > 0) {
                    // show all matches and hide those that don't match
                    context.not(":has(mark)").parent().parent().hide();
                    var toshow = context.has("mark").parent().parent();
                    var nmatches = toshow.length;
                    toshow.show();
                    console.log(toshow.length);
                    register_hover();
                    $("#copyasurl").addClass("badge");
                    $("#copyasurl").attr("data-badge",nmatches);
                } else {
                    context.parent().parent().show();
                    if (pattern.length > 0) {
                        $("#copyasurl").addClass("badge");
                        $("#copyasurl").attr("data-badge",0);
                    } else {
                        $("#copyasurl").removeClass("badge");
                    }
                }
            },
        });
    };

    var timer;
    var lastPattern = "";
    var timeoutms = 300;
    if (obj.length < 400) {
        timeoutms = 0;
    }
    $("input[id='filter']").keyup(function(e) {
        var pattern = $(this).val();
        if (pattern == lastPattern) return;
        if (lastPattern == "") {
            lastPattern = this.value;
            return;
        } else {
            lastPattern = this.value;
        }
        clearTimeout(timer);
        timer = setTimeout(function() {
            markre(lastPattern);
        }, timeoutms);
    });

    var handle = $( "#custom-handle" );
    // $("#slider").change(function() {
    $("#slider").bind("input",function() {
        var val = $(this).val();
        console.log(val);
        $("img").attr("height",300*val/100);
        if ((val == 0 && imagesVisible) || (val != 0 && !imagesVisible)) {
            toggleImages();
        }
    });


        var file_objects = make_objects(filelist);
        draw_objects(file_objects);

    // if page was loaded with a parameter for search, then simulate a search
    // ex: http://uaf-6.t2.ucsd.edu/~namin/dump/plots_isfr_Aug26/?HH$
    if(window.location.href.indexOf("?") != -1) {
        var search = unescape(window.location.href.split("?")[1]);
        $("#filter").val(search);
        markre($("#filter").val());
    }

    register_hover();
    add_links_to_description(file_objects);
    // register hover for links in description AFTER adding them
    register_description_hover();

});

// vimlike incsearch: press / to focus on search box
$(document).keydown(function(e) {
    // console.log($(event.target));
    // console.log(e.keyCode);
    var modifierNoShift = e.metaKey || e.ctrlKey || e.altKey;
    var modifier = e.shiftKey || modifierNoShift;
    if(e.keyCode == 191 && !modifier) {
        // / focus search box
        e.preventDefault();
        $("#filter").focus().select();
    }
    if (!$(event.target).is(":input")) {
        if(e.keyCode== 89 && !modifier) {
            // y
            getQueryURL();
        }
        if(e.keyCode == 71 && !modifierNoShift) {
            // G scrolls to bottom, g to top
            if (e.shiftKey) {
                window.scrollTo(0,document.body.scrollHeight);
            } else {
                window.scrollTo(0,0);
            }
        }
        if(e.keyCode == 83 && !modifierNoShift) {
            // s and shift S to sort a-z or z-a
            if (e.shiftKey) {
                $("#images").html($(".box").sort(function (a,b) { return $(a).attr("id").localeCompare($(b).attr("id")); }));
            } else {
                $("#images").html($(".box").sort(function (a,b) { return -$(a).attr("id").localeCompare($(b).attr("id")); }));
            }
        }
        if(e.keyCode == 77 && !modifier) {
            // m to toggle dark mode
            toggleDarkMode();
        }
        if(e.keyCode == 66 && !modifier) {
            // b to toggle super saturation mode
            toggleSaturation();
        }
        if(e.keyCode == 88 && !modifier) {
            // x to show and hide images
            toggleImages();
        }
    }
});

function copyToClipboard(text) {
    var $temp = $("<input>");
    $("body").append($temp);
    $temp.val(text).select();
    document.execCommand("copy");
    $temp.remove();
    $("#message").html("Copied to clipboard!").delay(600).queue(function(n) {$(this).html("");n();});
}

function getQueryURL() {
    var query = escape($('#filter').val());
    var queryURL = "http://"+location.hostname+location.pathname+"?"+query;
    console.log(queryURL);
    copyToClipboard(queryURL)
}

var darkMode = false;
function toggleDarkMode() {
    $(".has-dark").toggleClass("dark-mode");
    // $(".legendname").toggleClass("label-secondary");
    if (superSaturation) {
        toggleSaturation();
    }
    darkMode ^= true;
}

var superSaturation = false;
function toggleSaturation() {
    if (darkMode) {
        $(".innerimg").toggleClass("dark-mode-super-saturate");
    } else {
        $(".innerimg").toggleClass("super-saturate");
    }
    superSaturation ^= true;
}
var imagesVisible = true;
function toggleImages() {
    $("img").toggle();
    $("fieldset").toggleClass("noborder");
    imagesVisible ^= true;
}

</script>

</head>

<body class="has-dark">

    <div id="container">

        <div id="jstree_demo_div"> </div>

        <div class="has-icon-right" style="width: 200px; display: inline-block;">
            <input type="text" class="form-input input-sm inputbar" id="filter" placeholder="Search/filter" />
            <i class="form-icon"></i>
        </div>

        &nbsp;
        <a href="javascript:;" id="copyasurl" class='has-dark btn btn-sm' onClick="getQueryURL();" data-badge="">copy as URL</a> &nbsp;


        <div class="popover popover-bottom">
            <button class="btn btn btn-sm">help</button>
            <div class="popover-container">
                <div class="card">
                    <div class="card-header">
                        Keybindings
                    </div>
                    <div class="card-body">
                        <kbd>g</kbd> / <kbd>G</kbd> to scroll to top/bottom <br>
                        <kbd>/</kbd> to focus the search box <br>
                        <kbd>y</kbd> to copy the filter state as a URL <br>
                        <kbd>s</kbd> / <kbd>S</kbd> to sort A-Z/Z-A <br>
                        <kbd>b</kbd> to toggle super-saturation mode <br>
                        <kbd>m</kbd> to toggle dark mode <br>
                        <kbd>x</kbd> to toggle image visibility <br>
                    </div>
                    <div class="card-footer">
                        <a href="https://github.com/aminnj/niceplots">View my source on GitHub</a>
                    </div>
                </div>
            </div>
        </div>

        &nbsp;
        <input id="slider" class="slider input-sm tooltip tooltip-bottom" type="range" min="0" max="300" value="100" oninput="this.setAttribute('value', this.value);">
        <!-- <span id="message"></span> -->
<div id="description">
<?php
$description = @file_get_contents("description.txt");
if( $description ) {
    echo "<br><b>Description:</b><br>";
    echo $description;
}
?>
</div>
<div id="images"></div>
<div id="metadatacontainer"  style="text-align: center;">
<div id="metadata" style="display: inline-block; text-align: left; display: none">
</div>
</div>

<canvas id='hovercanvas' width="50" height="300"></canvas>
</div>


</body>
</html>
