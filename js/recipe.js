/*
 * Peter Farrar - 20170711
 *
 * Script to handle the height of the ingredients and/or description
 * fields, depending on their relative height to eachother, and the
 * size of the browser window.
 */
$( function(){
    /*
     * Resize based on window width
     */
    $(window).on("load resize", function () {
        /*
         * This next line was the key to fixing text overflow
         * durring resizing.  Without it, text will overflow 
         * from ingredients or decription into the next div field.
         */
        $('div').css('height', 'auto');

        var pageWidth = $(document).width();
        var iHeight = $('div.ingredients').height();
        var dHeight = $('div.description').height();

        /*
         * It appears that Bootstrap considers xs screens anything below 755
         */
        if ( pageWidth > 755 ) {
            /*
             * Theoretically, I only need to resize the smaller div.  But in reality
             * that doesn't seem to work quite right.  So I just resize both to the
             * same value.
             */
            if ( dHeight > iHeight ) {
                $('div.description').height(dHeight);
                $('div.ingredients').height(dHeight);
            } else {
                $('div.description').height(iHeight);
                $('div.ingredients').height(iHeight);
            }
        } else {
            /*
             * And here I reset to the original size... this is not a perfect system though.
             */
            $('div.description').height(dHeight);
            $('div.ingredients').height(iHeight);
        }
    }).trigger("resize");
});
