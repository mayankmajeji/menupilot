/**
 * MenuPilot Admin Pages JavaScript
 *
 * @package MenuPilot
 */

/* global jQuery, menupilot */
jQuery(document).ready(function ($) {
  "use strict";

  // Toggle vertical tabs collapse
  $(document).on(
    "click",
    ".mp-collapse-btn[data-mp-toggle='vtabs']",
    function () {
      const $grid = $(this).closest(".mp-2col");
      if ($grid.length) {
        $grid.toggleClass("is-collapsed");
      }
    }
  );

  // Export Page - Enable button when menu is selected
  $(document).on("change", 'input[name="menu_id"]', function () {
    $("#mp-export-btn").prop("disabled", false);
  });

  // Export Page - Handle form submission
  $(document).on("submit", "#mp-export-form", function (e) {
    e.preventDefault();

    const $form = $(this);
    const $button = $("#mp-export-btn");
    const $spinner = $("#mp-export-spinner");
    const $result = $("#mp-export-result");
    const menuId = $('input[name="menu_id"]:checked').val();

    if (!menuId) {
      $result.html(
        '<div class="notice notice-error"><p>Please select a menu to export.</p></div>'
      );
      return;
    }

    // Show spinner
    $button.prop("disabled", true);
    $spinner.addClass("is-active");
    $result.html("");

    // Make REST API request
    $.ajax({
      url: menupilot.restUrl + "/menus/export",
      type: "POST",
      beforeSend: function (xhr) {
        xhr.setRequestHeader("X-WP-Nonce", menupilot.nonce);
      },
      contentType: "application/json",
      data: JSON.stringify({
        menu_id: parseInt(menuId),
      }),
      success(response) {
        if (response.success && response.data) {
          // Create download link
          const jsonString = JSON.stringify(response.data, null, 2);
          const blob = new Blob([jsonString], {
            type: "application/json",
          });
          const url = window.URL.createObjectURL(blob);
          const a = document.createElement("a");
          a.href = url;
          a.download = response.filename || "menu-export.json";
          document.body.appendChild(a);
          a.click();
          window.URL.revokeObjectURL(url);
          document.body.removeChild(a);

          $result.html(
            '<div class="notice notice-success"><p>' +
              response.message +
              "</p></div>"
          );
        } else {
          $result.html(
            '<div class="notice notice-error"><p>' +
              (response.message || "Export failed. Please try again.") +
              "</p></div>"
          );
        }
      },
      error(xhr) {
        let message = "An error occurred. Please try again.";
        if (xhr.responseJSON && xhr.responseJSON.message) {
          message = xhr.responseJSON.message;
        }
        $result.html(
          '<div class="notice notice-error"><p>' + message + "</p></div>"
        );
      },
      complete() {
        $spinner.removeClass("is-active");
        $button.prop("disabled", false);
      },
    });
  });

  // Import Page - Handle file selection
  $(document).on("change", "#mp-menu-file", function () {
    const files = this.files;
    const $button = $("#mp-import-btn");

    if (files && files.length > 0) {
      const fileName = files[0].name;
      $("#mp-file-name").text(fileName);
      $("#mp-file-info").show();
      $button.prop("disabled", false);
      $(".mp-upload-area").addClass("has-file");
    } else {
      $("#mp-file-info").hide();
      $button.prop("disabled", true);
      $(".mp-upload-area").removeClass("has-file");
    }
  });

  // Import Page - Handle form submission (upload & preview)
  $(document).on("submit", "#mp-import-form", function (e) {
    e.preventDefault();

    const $form = $(this);
    const $button = $("#mp-import-btn");
    const $spinner = $("#mp-import-spinner");
    const $result = $("#mp-import-result");
    const fileInput = document.getElementById("mp-menu-file");

    if (!fileInput || !fileInput.files || !fileInput.files[0]) {
      $result.html(
        '<div class="notice notice-error"><p>Please select a file to import.</p></div>'
      );
      return;
    }

    // Show spinner
    $button.prop("disabled", true);
    $spinner.addClass("is-active");
    $result.html("");

    // Read file and generate preview client-side
    const reader = new FileReader();
    reader.onload = function (event) {
      try {
        // Parse JSON
        const importData = JSON.parse(event.target.result);

        // Validate structure
        if (!importData.menu || !importData.menu.name) {
          throw new Error("Invalid menu data structure");
        }

        // Generate preview HTML client-side
        const previewHtml = generateImportPreview(importData);

        // Show preview in modal
        showImportModal(previewHtml, importData);
        $result.html("");
      } catch (error) {
        $result.html(
          '<div class="notice notice-error"><p>Invalid JSON file. Please check the file format.</p></div>'
        );
      } finally {
        $spinner.removeClass("is-active");
        $button.prop("disabled", false);
      }
    };

    reader.onerror = function () {
      $result.html(
        '<div class="notice notice-error"><p>Failed to read the file.</p></div>'
      );
      $spinner.removeClass("is-active");
      $button.prop("disabled", false);
    };

    reader.readAsText(fileInput.files[0]);
  });

  // Generate import preview HTML client-side
  function generateImportPreview(importData) {
    const menu = importData.menu || {};
    const context = importData.export_context || {};

    const menuName = menu.name || "Untitled Menu";
    const menuSlug = menu.slug || "";
    const items = menu.items || [];
    const locations = menu.locations || [];

    const sourceUrl = context.site_url || "";
    const exportedAt = context.exported_at || "";
    const registeredLocations = menupilot.registeredLocations || {};

    let html = '<div class="mp-card">';
    html += "<h3>Import Preview</h3>";
    html += '<table class="widefat"><tbody>';
    html +=
      "<tr><th>Menu Name:</th><td><strong>" +
      escapeHtml(menuName) +
      "</strong></td></tr>";
    html +=
      "<tr><th>Menu Slug:</th><td><code>" +
      escapeHtml(menuSlug) +
      "</code></td></tr>";
    html += "<tr><th>Total Items:</th><td>" + items.length + "</td></tr>";

    if (sourceUrl) {
      html +=
        "<tr><th>Source Site:</th><td><code>" +
        escapeHtml(sourceUrl) +
        "</code></td></tr>";
    }

    html +=
      "<tr><th>Destination Site:</th><td><code>" +
      menupilot.siteUrl +
      "</code></td></tr>";

    if (exportedAt) {
      const date = new Date(exportedAt);
      html +=
        "<tr><th>Exported At:</th><td>" + date.toLocaleString() + "</td></tr>";
    }

    if (locations.length > 0) {
      html +=
        "<tr><th>Source Locations:</th><td><code>" +
        locations.join(", ") +
        "</code></td></tr>";
    }

    html += "</tbody></table></div>";

    // Import configuration
    html += '<div class="mp-card" style="margin-top:20px;">';
    html += "<h3>Import Configuration</h3>";
    html += "<p>Configure how this menu will be imported:</p>";
    html += '<div id="mp-import-execute-form">';
    html += '<table class="form-table"><tbody>';
    html +=
      '<tr><th scope="row"><label for="mp-import-menu-name">Menu Name:</label></th>';
    html +=
      '<td><input type="text" id="mp-import-menu-name" name="menu_name" value="' +
      escapeHtml(menuName) +
      '" class="regular-text" required />';
    html +=
      '<p class="description">Enter a name for the imported menu.</p></td></tr>';
    html +=
      '<tr><th scope="row"><label for="mp-import-location">Assign to Location:</label></th>';
    html +=
      '<td><select id="mp-import-location" name="location"><option value="">— Do not assign —</option>';

    for (const [locationId, locationName] of Object.entries(
      registeredLocations
    )) {
      html +=
        '<option value="' +
        escapeHtml(locationId) +
        '">' +
        escapeHtml(locationName) +
        "</option>";
    }

    html += "</select>";
    html +=
      '<p class="description">Optionally assign this menu to a theme location.</p></td></tr>';
    html += "</tbody></table>";
    html +=
      '<input type="hidden" id="mp-import-data" name="import_data" value="" />';
    html += "</div></div>";

    // Menu items preview
    html += '<div class="mp-card" style="margin-top:20px;">';
    html += "<h3>Menu Items Preview</h3>";
    html += '<table class="widefat striped"><thead><tr>';
    html += "<th>Title</th><th>Type</th><th>Object</th><th>URL</th>";
    html += "</tr></thead><tbody>";

    items.forEach(function (item) {
      const indent = item.parent_id > 0 ? "— " : "";
      html += "<tr>";
      html += "<td>" + escapeHtml(indent + item.title) + "</td>";
      html += "<td><code>" + escapeHtml(item.type) + "</code></td>";
      html += "<td><code>" + escapeHtml(item.object) + "</code></td>";
      html += "<td><small>" + escapeHtml(item.url) + "</small></td>";
      html += "</tr>";
    });

    html += "</tbody></table></div>";

    return html;
  }

  // Helper function to escape HTML
  function escapeHtml(text) {
    const map = {
      "&": "&amp;",
      "<": "&lt;",
      ">": "&gt;",
      '"': "&quot;",
      "'": "&#039;",
    };
    return String(text).replace(/[&<>"']/g, function (m) {
      return map[m];
    });
  }

  // Import Modal Functions
  function showImportModal(html, data) {
    // Create modal if it doesn't exist
    if (!$("#mp-import-modal").length) {
      $("body").append(`
        <div id="mp-import-modal" class="mp-modal-overlay">
          <div class="mp-modal">
            <div class="mp-modal-header">
              <h2>Import Menu Preview</h2>
              <button type="button" class="mp-modal-close" aria-label="Close">
                <span class="dashicons dashicons-no-alt"></span>
              </button>
            </div>
            <div class="mp-modal-body"></div>
            <div class="mp-modal-footer">
              <button type="button" class="button" id="mp-modal-cancel">Cancel</button>
              <button type="button" class="button button-primary" id="mp-modal-import">Import Menu</button>
              <span class="spinner"></span>
            </div>
          </div>
        </div>
      `);
    }

    // Update modal content
    $("#mp-import-modal .mp-modal-body").html(html);

    // Store import data (wait for DOM to be ready)
    setTimeout(function () {
      if ($("#mp-import-data").length) {
        $("#mp-import-data").val(JSON.stringify(data));
      } else {
        console.error("Import data field not found");
      }
    }, 100);

    // Show modal
    $("#mp-import-modal").addClass("is-active");
    $("body").addClass("modal-open");
  }

  function closeImportModal() {
    $("#mp-import-modal").removeClass("is-active");
    $("body").removeClass("modal-open");
  }

  // Close modal on overlay click, close button, or cancel
  $(document).on("click", "#mp-import-modal", function (e) {
    if (e.target === this) {
      closeImportModal();
    }
  });

  $(document).on("click", ".mp-modal-close, #mp-modal-cancel", function () {
    closeImportModal();
  });

  // Close modal on ESC key
  $(document).on("keydown", function (e) {
    if (e.key === "Escape" && $("#mp-import-modal").hasClass("is-active")) {
      closeImportModal();
    }
  });

  // Handle Enter key in input fields to trigger import
  $(document).on("keypress", "#mp-import-menu-name", function (e) {
    if (e.which === 13) {
      e.preventDefault();
      $("#mp-modal-import").trigger("click");
    }
  });

  // Import Modal - Handle import execution
  $(document).on("click", "#mp-modal-import", function (e) {
    e.preventDefault();
    e.stopPropagation();

    console.log("Import button clicked");

    const $button = $(this);
    const $spinner = $("#mp-import-modal .mp-modal-footer .spinner");
    const $result = $("#mp-import-result");
    const menuName = $("#mp-import-menu-name").val();
    const location = $("#mp-import-location").val();
    const importData = $("#mp-import-data").val();

    console.log("Menu name:", menuName);
    console.log("Location:", location);
    console.log("Import data length:", importData ? importData.length : 0);

    if (!menuName) {
      alert("Please enter a menu name.");
      return false;
    }

    if (!importData) {
      alert("Import data is missing. Please try uploading the file again.");
      closeImportModal();
      return false;
    }

    // Show spinner
    $button.prop("disabled", true);
    $("#mp-modal-cancel").prop("disabled", true);
    $spinner.addClass("is-active");
    $result.html("");

    // Parse import data
    let menuData;
    try {
      menuData = JSON.parse(importData);
    } catch (error) {
      alert("Invalid import data. Please try uploading the file again.");
      closeImportModal();
      return false;
    }

    // Make REST API request
    $.ajax({
      url: menupilot.restUrl + "/menus/import",
      type: "POST",
      beforeSend: function (xhr) {
        xhr.setRequestHeader("X-WP-Nonce", menupilot.nonce);
      },
      contentType: "application/json",
      data: JSON.stringify({
        menu_name: menuName,
        menu_data: menuData,
        location: location,
      }),
      success(response) {
        if (response.success) {
          closeImportModal();
          $result.html(
            '<div class="notice notice-success"><p>' +
              response.message +
              ' <a href="' +
              response.edit_url +
              '">Edit menu</a></p></div>'
          );
          // Reset form
          $("#mp-menu-file").val("");
          $("#mp-file-info").hide();
          $("#mp-import-btn").prop("disabled", true);
          $(".mp-upload-area").removeClass("has-file");

          // Scroll to result
          $("html, body").animate(
            { scrollTop: $result.offset().top - 100 },
            300
          );
        } else {
          $result.html(
            '<div class="notice notice-error"><p>' +
              (response.message || "Import failed. Please try again.") +
              "</p></div>"
          );
        }
      },
      error(xhr) {
        let message = "An error occurred during import. Please try again.";
        if (xhr.responseJSON && xhr.responseJSON.message) {
          message = xhr.responseJSON.message;
        }
        $result.html(
          '<div class="notice notice-error"><p>' + message + "</p></div>"
        );
      },
      complete() {
        $spinner.removeClass("is-active");
        $button.prop("disabled", false);
        $("#mp-modal-cancel").prop("disabled", false);
      },
    });
  });

  // Drag and drop support for file upload
  const $uploadArea = $(".mp-upload-area");
  if ($uploadArea.length) {
    $uploadArea
      .on("dragover", function (e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).addClass("dragover");
      })
      .on("dragleave", function (e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass("dragover");
      })
      .on("drop", function (e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass("dragover");

        const files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
          document.getElementById("mp-menu-file").files = files;
          $("#mp-menu-file").trigger("change");
        }
      });
  }
});
