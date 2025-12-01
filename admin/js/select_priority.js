$(document).ready(function(){
    $.ajax({
        url: 'get_priority.php',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            let options = '<option value="">-- Select Priority --</option>';
            data.forEach(function(types) {
                options += `<option value="${types.priority_id  }">${types.priority_name}</option>`;
            });
            $('#priority').html(options);
        },
        error: function(xhr, status, error) {
            $('#priority').html('<option>Error loading Priority</option>');
            console.error(error);
        }
    });
});