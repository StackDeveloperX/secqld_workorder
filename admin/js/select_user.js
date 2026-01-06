$(document).ready(function(){
    $.ajax({
        url: 'get_users.php',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            let options = '<option value="">-- Select User --</option>';
            data.forEach(function(types) {
                options += `<option value="${types.client_id}">${types.business_name}</option>`;
            });
            $('#user_select').html(options);
        },
        error: function(xhr, status, error) {
            $('#user_select').html('<option>Error loading types</option>');
            console.error(error);
        }
    });
});

$(document).ready(function(){
    $.ajax({
        url: 'get_users.php',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            let options = '<option value="">-- Select User --</option>';
            data.forEach(function(types) {
                options += `<option value="${types.client_id}">${types.business_name}</option>`;
            });
            $('#user_select_recurring').html(options);
        },
        error: function(xhr, status, error) {
            $('#user_select_recurring').html('<option>Error loading types</option>');
            console.error(error);
        }
    });
});