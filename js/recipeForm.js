$(function() {
    //  This will manage adding and removing fields to the addRecipe/editRecipe page form
    var buttonAction = {
        mark_edited : function (thisClass) {
            // update an attribute in the container div if there is a change or addition/subtraction...
            $('div.'+ thisClass +'s').attr('edited', true);
            $('input[name='+thisClass+'s]').attr('value', 'edited');
        },
        remove_field : function ( fieldName ) {
            // remove or clear, depending on if there are more than one of this type of field left.
            thisField = $("textarea[name='"+ fieldName +"']");
            parentField = thisField.parent();
            parentClass = parentField.attr('class').split(' ');
            parentSiblingCnt = $('div.'+ parentClass).length;

            buttonAction.mark_edited(parentClass[0]);

            if (parentSiblingCnt > 1 ) {  
                parentField.remove();
            } else {
                thisField.val('');
            }
        },
        add_field : function ( fieldName ) {
            var thisField = $('textarea[name=' + fieldName +']').parent();
            
            // Update an attribute in the container div if there is a change or addition/subtraction...
            // Otherwise, the backend PHP will not update the DB.
            var className = fieldName.split('_')[0];
            buttonAction.mark_edited(className);

            // Use the thisField element to create a new field... 
            // Not sure why .text('') works, but .var('') doesn't.  I thought it would be the other way around.  huh.
            var newField = thisField.clone(); 
            newField.find('textarea').text('');

            // (getting some weird results that have led to the <div class=... solution when adding new field)
            thisField.after("<div class=\""+ className +" form-group input-group\">"+ newField.html() +"</div>");

            buttonAction.renumber_fields(className);
            buttonAction.add_button_action(className);
        },
        add_button_action: function ( className ) {
            // Remove existing event handler
            $('div.'+ className +' button.plus').off("click");
            $('div.'+ className +' button.minus').off("click");
            
            // Then add button functionality to all buttons of this class
            $('div.'+ className +' button.plus').each(function(){
                var taName = $(this).closest('div').find('textarea').attr('name');
                $(this).click(function(){ buttonAction.add_field(taName) });
            });
            $('div.'+ className +' button.minus').each(function(){
                var taName = $(this).closest('div').find('textarea').attr('name');
                $(this).click(function(){ buttonAction.remove_field(taName) });
            });
        },
        renumber_fields: function ( className ) {
            // After adding a field, renumber the fields.  This actually simplified a lot of the add_field method.
            var cnt = 1;
            $('textarea.'+ className).each( function() {
                $(this).attr('name', className +"_"+ cnt);
                cnt ++;
            });
        }
    };

    // initialize buttons
    var classList = ["description", "ingredient", "step"];
    classList.forEach(buttonAction.add_button_action);

    // update an attribute in the container div if there is a change or addition/subtraction...
    $('textarea').on('input propertychange paste', function() { 
        thisClass = $(this).attr('class').split(' ')[0];
        buttonAction.mark_edited(thisClass);
    });

    // for screens sm and xs, increase the form field size.
    if ( screen.width < 922 ) {
        $('form').addClass('form-group-lg');
    }
});
