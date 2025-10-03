function showSweetAlert(title, text, icon, redirectUrl = "") {
    Swal.fire({
        title: title,
        text: text,
        icon: icon,
        timer: 5000,
        timerProgressBar: true,
        showConfirmButton: true,
        confirmButtonColor: "#a83232", 
        cancelButtonColor: "#555555",
        customClass: {
            confirmButton: 'swal2-confirm-custom',
            cancelButton: 'swal2-cancel-custom'
        },
        willClose: () => {
            if (redirectUrl) {
                window.location.href = redirectUrl;
            }
        }
    });
}

function confirmDeletion(table, delete_column, delete_ID, URL) {
    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "Cancel",
        confirmButtonColor: "#a83232", 
        cancelButtonColor: "#555555",
        customClass: {
            confirmButton: 'swal2-confirm-custom',
            cancelButton: 'swal2-cancel-custom'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            delete_Record(table, delete_column, delete_ID, URL);
        }
    });
}

function delete_Record(table, delete_column, delete_ID, URL) {
    console.log("Deleting record with data:", {
        action: "delete_Record",
        table: table,
        delete_column: delete_column,
        delete_ID: delete_ID
    });

    $.ajax({
        type: 'POST',
        url: '../php/processes.php',
        data: { 
            action: "delete_Record", 
            table: table,
            delete_column: delete_column,
            delete_ID: delete_ID
        },
        dataType: 'json',
        success: function(response) {
            console.log("Delete request response:", response);
            if (response.success) {
                showSweetAlert("Success", response.message, "success", URL);
            } else {
                showSweetAlert("Error", response.message, "error");
            }
        },
        error: function(xhr, status, error) {
            console.error("AJAX Error:", xhr.responseText);
            showSweetAlert("Error", "An error occurred while trying to delete the record. Please try again.", "error");
        }
    });
}

function confirmMultipleDeletion(count, callback) {
    Swal.fire({
        title: "Are you sure?",
        text: `You are about to delete ${count} record(s). This action cannot be undone!`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete all!",
        cancelButtonText: "Cancel",
        confirmButtonColor: "#a83232", 
        cancelButtonColor: "#555555", 
        customClass: {
            confirmButton: 'swal2-confirm-custom',
            cancelButton: 'swal2-cancel-custom'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            callback(); // Proceed to deletion
        }
    });
}

function deleteMultipleRecords(table_name, delete_column_ID, selectedIDs, URL) {
  $.ajax({
      type: 'POST',
      url: '../php/processes.php',
      data: { 
          action: "delete_Record", 
          table: table_name, 
          delete_column: delete_column_ID, 
          delete_IDs: JSON.stringify(selectedIDs)
      },
      dataType: 'json',
      success: function(response) {
          console.log("Delete response:", response);
          if (response.success) {
              showSweetAlert("Deleted!", response.message, "success", URL);
          } else {
              showSweetAlert("Error", response.message, "error");
          }
      },
      error: function(xhr, status, error) {
          console.error("AJAX Error:", xhr.responseText);
          showSweetAlert("Error", "An error occurred while deleting the records.", "error");
      }
  });
}

function archiveRecord(table, delete_column, delete_ID, URL) {
    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "Cancel",
        confirmButtonColor: "#a83232", 
        cancelButtonColor: "#555555", 
        customClass: {
            confirmButton: 'swal2-confirm-custom',
            cancelButton: 'swal2-cancel-custom'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            archive_Record(table, delete_column, delete_ID, URL);
        }
    });
}

function archive_Record(table, delete_column, delete_ID, URL) {
    console.log("Deleting record with data:", {
        action: "delete_Record",
        table: table,
        delete_column: delete_column,
        delete_ID: delete_ID
    });

    $.ajax({
        type: 'POST',
        url: '../php/processes.php',
        data: { 
            action: "archive_Record", 
            table: table,
            delete_column: delete_column,
            delete_ID: delete_ID
        },
        dataType: 'json',
        success: function(response) {
            console.log("Delete request response:", response);
            if (response.success) {
                showSweetAlert("Success", response.message, "success", URL);
            } else {
                showSweetAlert("Error", response.message, "error");
            }
        },
        error: function(xhr, status, error) {
            console.error("AJAX Error:", xhr.responseText);
            showSweetAlert("Error", "An error occurred while trying to delete the record. Please try again.", "error");
        }
    });
}

