$(function(){
    // catch any message returned from a submit and show modal
    var message = $('div.modal-message');
    var redirect = $('div.modal-redirect').text();
    //var redirect = message.attr('data-redirect');
    if ( message.text() ) {
        var type = message.attr('type');
        var label = type.split("-")[1];
        $('h1.message').text(message.text());
        $('h1.message').addClass(type);
        $('button#default').addClass('hidden');
        $('button#continue').on('click', function() { $('button#default').click();
            if ( redirect ) {
                location.href = redirect;
                console.log(redirect);
            }
        });
        $('button[data-toggle="modal"]').click();
        $('#myModalLabel').text(label);
    } else {
        $('button#continue').on('click', function() { 
            $('button[data-dismiss=modal]').click();
            $('.confirmation').removeClass('hidden');
        });
    }

    // Bind any submit buttons to modal
    $('form').each(function() {
        var message = $(this).find('.submit-message').text();
        var continueBtn = $(this).find('input.btnSubmit');
        $(this).find('.login-btn').on('click', function() {
            // first, clear any hidden buttons and returned message types
            $('h1.message').removeClass('text-warning');
            $('h1.message').removeClass('text-info');
            $('button#default').removeClass('hidden');
            $('button#continue').removeClass('hidden');

            // bind to buttons
            $('button#continue').unbind('click');
            $('button#continue').on('click', function() { continueBtn.click(); });

            // show modal
            $('#myModalLabel').text("Verify Action");
            $('h1.message').text(message);
            // here you can add some code to parse the message and do a substitute.
            // for example %username% could be replaced by something like: $(this).parent().find('input[name="'+ inputFieldName +'"]')
            // where inputFieldName was pulled from a match on /%(.*)%/.. however you do that in javascript.
            $('button[data-toggle="modal"]').click();
        })
    });
});

