var isLeft = false;
var isAnimating = false;
var sideBarCheck = false;
var currentDropDown = null;
var triggeredElementList = null;
var searchOptShown = false;
var searchOptSelected = 'anything';
var json;

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
});

/**
 * Parses the JSON from the header of the application page.
 */
function parseJson() {
    json = JSON.parse($("#app-json").html());
}

/**
 * Toggles the side bar from size to size.
 * @param {object} e - Element instance.
 */
function toggleSideBar(e) {
    var initial = true;
    var collapseIcon = $(e).data('collapse');
    var expandIcon = $(e).data('expand');
    var sideBar = $("#" + $(e).data('side-bar'));
    var fullLinks = $("#" + $(e).data('full'));
    var condensedLinks = $("#" + $(e).data('condensed'));
    var icon = $("#" + $(e).data('icon'));
    var toggleLink = $("#" + $(e).data('link'));
    var bottomFull = $("#" + $(e).data('bottom-links-full'));
    var bottomMin = $("#" + $(e).data('bottom-links-min'));
 
    if (!isAnimating) {
        isAnimating = true;

        if (isLeft) {
            fullLinks.hide();
            condensedLinks.show();
            bottomMin.css({'display':'block'});
            bottomFull.hide();
            bottomMin.show();
            bottomMin.css({'display':'inline'});

            icon.removeClass(expandIcon).addClass(collapseIcon);

            sideBar.animate({
                right: '-=240px',
                width: '60px'
            }, 700);

            toggleLink.animate({
                left: '60px'
            }, 700, function() {
                isAnimating = false;
            });
        } else {
            setTimeout(function() {
                fullLinks.show();
                condensedLinks.hide();
                bottomFull.show();
                bottomMin.hide();
            }, 1000);

        icon.removeClass(collapseIcon).addClass(expandIcon);
        
            sideBar.animate({
                right: '+=240px',
                width: '210px'
            }, 700);

            toggleLink.animate({
                left: '210px'
            }, 700, function() {
                isAnimating = false;
            });
        }
    
        isLeft = !isLeft;
    }
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
 * Displays the search options selection.
 */
function showSearchOptions(e) {
    if (!searchOptShown) {
        $("#" + $(e).data('options')).fadeIn();
    }
}

/**
 * Triggered when the top search button is clicked.
 * @param {object} e - Element instance. 
 */
function searchOptionSelect(e) {
    var optionText = $("#" + $(e).data('text'));
    var selectedOption = $(e).data('selected');
    var checkbox = $(e).data('checkbox');

    optionText.text(selectedOption);

    $("#so-icon-" + searchOptSelected.toLowerCase()).html('');
    $("#so-" + searchOptSelected.toLowerCase()).removeClass(json.search_opt_selected_class);
    $("#so-icon-" + selectedOption.toLowerCase()).html('<i class="' + checkbox + '"></i> ');
    $("#so-" + selectedOption.toLowerCase()).addClass(json.search_opt_selected_class);

    searchOptSelected = $(e).data('selected');
}