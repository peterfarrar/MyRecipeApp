/*
 * Peter Farrar 20170719
 *
 * Simple script to insert a search field into the bootstrap nav bar.
 */
$( function(){
    var thisUrl = $(location).attr('href');
    // strip off query string
    thisUrl = thisUrl.split('?')[0];

    var searchHtml = '<div class="col-sm-3 col-md-3" style="float: right">';
    searchHtml += '<form class="navbar-form" role="search" method="get" action="'+ thisUrl +'">';
    searchHtml += '<div class="input-group">';
    searchHtml += '<input type="hidden" name="page" value="showMenu" class="search">';
    searchHtml += '<input class="form-control" placeholder="Search" name="search" type="text">';
    searchHtml += '<div class="input-group-btn">';
    searchHtml += '<button class="btn btn-default" type="submit"><i class="glyphicon glyphicon-search"></i></button>';
    searchHtml += '</div>';
    searchHtml += '</div>';
    searchHtml += '</form>';
    searchHtml += '</div>';

    $('.navbar-nav').after(searchHtml);
});

