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

    // Make AJAX request
    $.ajax({
      url: menupilot.ajaxurl,
      type: "POST",
      data: {
        action: "menupilot_export_menu",
        nonce: menupilot.nonce,
        menu_id: menuId,
      },
      success(response) {
        if (response.success && response.data.json) {
          // Create download link
          const blob = new Blob([response.data.json], {
            type: "application/json",
          });
          const url = window.URL.createObjectURL(blob);
          const a = document.createElement("a");
          a.href = url;
          a.download = response.data.filename || "menu-export.json";
          document.body.appendChild(a);
          a.click();
          window.URL.revokeObjectURL(url);
          document.body.removeChild(a);

          $result.html(
            '<div class="notice notice-success"><p>' +
              response.data.message +
              "</p></div>"
          );
        } else {
          $result.html(
            '<div class="notice notice-error"><p>' +
              (response.data && response.data.message
                ? response.data.message
                : "Export failed. Please try again.") +
              "</p></div>"
          );
        }
      },
      error() {
        $result.html(
          '<div class="notice notice-error"><p>An error occurred. Please try again.</p></div>'
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
    const fileName = $(this).val().split("\\").pop();
    const $button = $("#mp-import-btn");

    if (fileName) {
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
    const $preview = $("#mp-import-preview");
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
    $preview.hide();

    // Read file and send via AJAX
    const reader = new FileReader();
    reader.onload = function (event) {
      $.ajax({
        url: menupilot.ajaxurl,
        type: "POST",
        data: {
          action: "menupilot_preview_import",
          nonce: menupilot.nonce,
          json_data: event.target.result,
        },
        success(response) {
          if (response.success && response.data.html) {
            $preview.html(response.data.html).show();
            $result.html("");
          } else {
            $result.html(
              '<div class="notice notice-error"><p>' +
                (response.data && response.data.message
                  ? response.data.message
                  : "Invalid import file. Please check the file and try again.") +
                "</p></div>"
            );
          }
        },
        error() {
          $result.html(
            '<div class="notice notice-error"><p>An error occurred while processing the file.</p></div>'
          );
        },
        complete() {
          $spinner.removeClass("is-active");
          $button.prop("disabled", false);
        },
      });
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
