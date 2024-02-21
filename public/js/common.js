var isLeft = false;
var isAnimating = false;
var sideBarCheck = false;
var currentDropDown = null;
var triggeredElementList = null;
var searchOptShown = false;
var searchOptSelected = {};
var json;
var currentDialogElement = null;
var currentForumFilter = null;
var ajaxUrl;
var selectedForum = null;
var topicsLoadLimit = 0;
var topicsCurrentIndex = 0;

/**
 * Items in here execute when the document is ready.
 */
$(document).ready(function() {
    // Check for things when the user clicks on the page.
    $(this).click(function(e) {
        var found = false;

        for (var i = 0; i < triggeredElementList.length; i++) {
            if (e.target.id === triggeredElementList[i]) {
                found = true;
                break;
            }
        }

        if (!found) {
            $("#" + currentDropDown).slideUp();
            currentDropDown = null;
            triggeredElementList = null;
        }
    });

    $('[data-toggle="tooltip"]').tooltip();
    parseJson();

    if (json.tag_cloud != null) {
        if (json.tag_cloud) {
            generateTagCloud();
        }
    }

    ajaxUrl = json.wrapper;
});

/**
 * Parses the JSON from the header of the application page.
 */
function parseJson() {
    json = JSON.parse($("#app-json").html());
}

/**
 * Opens a specified drop down menu underneath or above the element.
 * @param {object} e - Element instance. 
 */
function openDropDownMenu(e)  {
    var menu = $("#" + $(e).data('menu'));
    var ignored = $(e).data('ignored');
    var ignoredElements = ignored.split(',');
    var linkElement = $("#" + $(e).data('link'));
    var movement = $(e).data('movement');

    if (currentDropDown != null) {
        $("#" + currentDropDown).slideUp();
        currentDropDown = null;
        triggeredElementList = null;
    }

    console.log(ignoredElements);

    var difference = ($(window).width() - $("#" + ignoredElements[0]).offset().left);
    var spaceBelow = $(window).height() - (linkElement.offset().top + linkElement.height() + 5);

    if (menu.width() >= difference || spaceBelow < menu.height()) {
        menu.css({'left':(linkElement.offset().left - menu.width() + linkElement.width() + 'px')});
        menu.css({'top':(linkElement.offset().top - linkElement.height() - 10 + (typeof(movement) !== 'undefined' ? movement : '')) + 'px'});
    } else {
        menu.css({'left':linkElement.offset().left + 'px'});
        menu.css({'top':(linkElement.offset().top + linkElement.height() + 10 + (typeof(movement) !== 'undefined' ? movement : '')) + 'px'});
    }

    if (linkElement.parent().css('display') === 'flex') {
        menu.css({'left':(linkElement.position().left + 'px')});
    }

    menu.slideDown();
    ignoredElements.push($(e).data('link'));
    currentDropDown = $(e).data('menu');
    triggeredElementList = ignoredElements;
}

/**
 * Sets the default values for the given options.
 * @param {object} list - List of the options to set. 
 */
function setAllDefaultOptions(list) {
    for (var key in list) {
        searchOptSelected[key] = list[key];
    }
}

/**
 * Selects a given option from a drop down menu.
 * @param {object} e - Element instance. 
 */
function optionSelect(e) {
    var optionText = $("#" + $(e).data('text'));
    var selectedOption = $(e).data('selected');
    var checkbox = $(e).data('checkbox');
    var icon = $(e).data('icon');
    var item = $(e).data('item');
    var id = $(e).data('id');

    optionText.text(selectedOption);

    var searchOption = searchOptSelected[id];

    if (typeof searchOption === 'string') {
        var iconId = icon + searchOption.toLowerCase();
        var itemId = item + searchOption.toLowerCase();
        $("#" + iconId).html('');
        $("#" + itemId).removeClass(json.opt_selected_class);
    } else {
        // Handle the case when searchOption is not a string
        console.error("searchOption is not a string:", searchOption);
    }

    var selectedIconId = icon + selectedOption.toLowerCase();
    var selectedItemID = item + selectedOption.toLowerCase();
    $("#" + selectedIconId).html('<i class="' + checkbox + '"></i>');
    $("#" + selectedItemID).addClass(json.opt_selected_class);

    searchOptSelected[id] = $(e).data('selected');
}

/**
 * Generates the tag cloud using random font sizes.
 */
function generateTagCloud() {
    var tags = document.querySelectorAll('.tag');
    console.log(tags);
    
    tags.forEach(function(tag) {
        var fontSize = Math.floor(Math.random() * 20) + 12;
        tag.style.fontSize = fontSize + 'px';
    });
}

/**
 * Toggles the background disabler element.
 * @param {string} mode - Mode to perform. 
 */
function toggleBackgroundDisabler(mode) {
    if (mode == 'show') {
        $("#background-disabler").fadeIn();
    } else {
        $("#background-disabler").fadeOut();
    }
}

/**
 * Opens the specified dialog element.
 */
function openDialog() {
    var dialog = null;
    var dialogWidth = null;

    if (arguments.length == 1) {
        dialog = $("#" + $(arguments[0]).data('dialog'));
        dialogWidth = $("#" + $(arguments[1].data('width')));
    } else {
        dialog = $("#" + arguments[0]);
        dialogWidth = arguments[1];
    }

    dialog.css({"width":dialogWidth + "px"});

    if (currentDialogElement != null) {
        $("#" + currentDialogElement).fadeOut();
        toggleBackgroundDisabler();
        currentDialogElement = null;
    }

    toggleBackgroundDisabler('show');
    dialog.fadeIn({queue: false, duration: 'slow'});
    dialog.animate({'marginTop':'+=30px'}, 400, 'easeInQuad');
    currentDialogElement = dialog.attr('id');
}

/**
 * Closes the current open dialog element.
 */
function closeDialog() {
    if (currentDialogElement != null) {
        $("#" + currentDialogElement).fadeOut({queue: false, duration: 'slow'});
        $("#" + currentDialogElement).animate({'marginTop':'-=30px'}, 400, 'easeOutQuad');
        toggleBackgroundDisabler();
        currentDialogElement = null;
    }
}

/**
 * Capitalizes the first letter of the given string and makes the rest
 * lower case.
 * @param {string} inputString - String to capitalize the first letter for.
 * @return {string} - Modified string.
 */
function firstToUpper(inputString) {
    return inputString.charAt(0).toUpperCase() + inputString.slice(1).toLowerCase();
}

/**
 * Changes the forum filter for the user.
 * @param {object} e - Element instance object.
 */
function forumFilterSelect(e) {
    var item = $(e).data('item');
    var name = firstToUpper(item);
    var button = $("#forum-filter-button");
    var nameLower = name.toLowerCase();
    var spanElement = $("#forum-filter-span-" + nameLower);
    var iconElement = $("#forum-filter-icon-" + nameLower);
    var currentSpan = $("#forum-filter-span-" + currentForumFilter);
    var currentIcon = $("#forum-filter-icon-" + currentForumFilter);
    var postData = {
        filter: nameLower,
        forum: selectedForum
    };

    ajaxPost('setforumfilter', postData, function(response) {
        checkForError(response);
        button.html(name);
        currentSpan.removeClass(json.forum_filter_sel_class);
        currentIcon.html('ddd');
        spanElement.addClass(json.forum_filter_sel_class);
        iconElement.html(atob(json.checkmark_icon));
        currentForumFilter = nameLower;
        console.log(response.filter);
        $("#ajax-topics-content").html(response.topics);
        topicsCurrentIndex = topicsLoadLimit;
        $("#ajax-topics-lm-button").show();
        console.log(response.info);
    });
}

/**
 * Loads more topics via AJAX request.
 */
function loadMoreTopics() {
    var getData = {
        forumid: selectedForum,
        index: topicsCurrentIndex
    };

    ajaxGet('loadmoretopics', getData, function(response) {
        checkForError(response);
        console.log(response);
        if (response.hideButton) {
            $("#ajax-topics-lm-button").hide();
        }

        topicsCurrentIndex = response.index;

        $("#ajax-topics-content").append(response.topics);
    });
}

/**
 * Sends the user to the specified URL address.
 * @param {e} e - Element instance. 
 */
function onDivClick(e) {
    var url = $(e).data('url');
    window.location.href = url;
}