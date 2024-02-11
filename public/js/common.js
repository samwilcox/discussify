var isLeft = false;
var isAnimating = false;
var sideBarCheck = false;

$(document).ready(function() {
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
            
            // if (sideBar.position().left === 0 && !sideBarCheck) {
            //     sideBar.css({
            //         'width': '300px',
            //         'right': '+=240px'
            //     });

            //     sideBar.animate({
            //         right: '-=240px',
            //         width: '210px'
            //     }, 700);

            //     toggleLink.animate({
            //         left: '210px'
            //     }, 700, function() {
            //         isAnimating = false;
            //     });

            //     sideBarCheck = true;
            // } else {
                sideBar.animate({
                    right: '+=240px',
                    width: '210px'
                }, 700);

                toggleLink.animate({
                    left: '210px'
                }, 700, function() {
                    isAnimating = false;
                });
           // }
        }
    
        isLeft = !isLeft;
    }
}