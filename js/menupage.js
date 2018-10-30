$(function() {
    var sortMenu = function( column ) {
        var header = $('.menu-page-menu').find('div.header.row');
        var rows   = $('.menu-page-menu>div.recipe');

        var findSort    = '#'+ column +'_sort';
        var headerField = header.find(findSort);
        var result      = headerField.hasClass('glyphicon-chevron-up');

        if ( result === true ) {
            // chevron is up
            headerField.removeClass('glyphicon-chevron-up').addClass('glyphicon-chevron-down');
        } else {
            // chevron is down
            headerField.removeClass('glyphicon-chevron-down').addClass('glyphicon-chevron-up');
        }

        rows.sort(function(a, b) {
            var aText = $(a).find("."+ column).text();
            var bText = $(b).find("."+ column).text();

            // Could probably be done differently... but this works for now.
            // Maybe revisit and do the aText/bText in a conditional, then just the default return
            switch(column) {
                case 'date' :
                    var aDateArray  = aText.split("/");
                    var bDateArray  = bText.split("/");
                    var aDateString = aDateArray[2] + aDateArray[0] + aDateArray[1];
                    var bDateString = bDateArray[2] + bDateArray[0] + bDateArray[1];

                    if ( result === true ) {
                        return aDateString.localeCompare(bDateString);
                    } else {
                        return bDateString.localeCompare(aDateString);
                    }
                    break;
                default:
                    if ( result === true ) {
                        return aText.localeCompare(bText);
                    } else {
                        return bText.localeCompare(aText);
                    }
                    break;
            }
        });

        // wipe old rows and insert rows in the new order
        var oldRows   = $('.menu-page-menu>div.recipe');
        oldRows.each(function(){$(this).remove();});
        rows.each(function(){
            $(this).insertAfter('.header');
        });
    };

    var sort_recipe = $('span#recipe_header').parent();
    var sort_author = $('span#author_header').parent();
    var sort_date   = $('span#date_header').parent();

    sort_recipe.css({'background-color': '#78b5ff', 'border-style': 'solid', 'border-color': '#FFFFFF', 'border-width': '1px'});
    sort_author.css({'background-color': '#78b5ff', 'border-style': 'solid', 'border-color': '#FFFFFF', 'border-width': '1px'});
    sort_date.css({'background-color': '#78b5ff', 'border-style': 'solid', 'border-color': '#FFFFFF', 'border-width': '1px'});

    sort_recipe.click(function(){sortMenu('recipe')});
    sort_author.click(function(){sortMenu('author')});
    sort_date.click(function(){sortMenu('date')});
});
