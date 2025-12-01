$(document).ready(function(){
    $.ajax({
        url: 'get_types.php',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            let options = '<option value="">-- Select Type --</option>';
            data.forEach(function(types) {
                options += `<option value="${types.service_id }">${types.service_name}</option>`;
            });
            $('#service_type').html(options);
        },
        error: function(xhr, status, error) {
            $('#service_type').html('<option>Error loading types</option>');
            console.error(error);
        }
    });
});

$(document).ready(function(){
    $.ajax({
        url: 'get_types_recurring.php',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            let options = '<option value="">-- Select Type --</option>';
            data.forEach(function(types) {
                options += `<option value="${types.service_id }">${types.service_name}</option>`;
            });
            $('#service_type_recurring').html(options);
        },
        error: function(xhr, status, error) {
            $('#service_type_recurring').html('<option>Error loading types</option>');
            console.error(error);
        }
    });
});