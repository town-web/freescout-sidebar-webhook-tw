$(document).ready(function () {
  const searchInput = $("#search-input");
  const globalSearchResults = $("#global-search-results");
  const clientResults = $("#client-results");
  const cancelButton = $("#cancel-button");
  let clients = [];
  let user = "";
  let searchTerm = "";
  let selectedClient = {};

  searchInput.on("input", function () {
    searchTerm = $(this).val();
    if (searchTerm.length >= 2) {
      getSearchResults(searchTerm);
    } else {
      clearResults();
    }
  });

  cancelButton.on("click", function () {
    clearResults();
    searchInput.val("");
  });

  $("#client-contact-form").on("submit", function (event) {
    event.preventDefault();
    $("#add-client-contact-button").button("loading");
    const data = {
      first_name: $("#first_name").val(),
      last_name: $("#last_name").val(),
      email: $("#email").val(),
      phone: $("#phone").val(),
      address: $("#address").val(),
      title: $("#title").val(),
      type: $("#type").val(),
      main: Number($("#main").prop("checked")),
      billing: Number($("#billing").prop("checked")),
      client_id: selectedClient.id,
      municipality_id: selectedClient.municipality_id
    };
    searchContact(data.email).then((contacts) => {
      if (!contacts.length && data.municipality_id) {
        addContact(data).then((contact) => {
          data.contact_id = contact.id;
          addClientContact(data);
        });
      } else {
        if (contacts.length) data.contact_id = contacts[0].id;
        addClientContact(data);
      }
    });
  });

  function addClientContact(data) {
    fsAjax(
      {
        action: "addClientContact",
        data,
        mailbox_id: getGlobalAttr("mailbox_id"),
        conversation_id: getGlobalAttr("conversation_id"),
      },
      laroute.route("sidebarwebhook.ajax"),
      function (response) {
        if (isAjaxSuccess(response)) {
          ajaxFinish();
          showFloatingAlert(
            "success",
            "Contact added to client successfully",
            true
          );
          setTimeout(function () {
            location.reload();
          }, 1000);
        } else {
          ajaxFinish();
          showAjaxError(response);
        }
      },
      true
    );
  }

  function searchContact(searchTerm) {
    return new Promise((resolve, reject) => {
      fsAjax(
        {
          action: "searchContact",
          search: searchTerm,
          mailbox_id: getGlobalAttr("mailbox_id"),
          conversation_id: getGlobalAttr("conversation_id"),
        },
        laroute.route("sidebarwebhook.ajax"),
        function (response) {
          if (response.status == "success" && response.data) {
            const data = JSON.parse(response.data || "{}");
            resolve(data);
          } else {
            ajaxFinish();
            showAjaxError(response);
          }
        },
        true
      );
    });
  }

  function addContact(data) {
    return new Promise((resolve, reject) => {
      fsAjax(
        {
          action: "addContact",
          data,
          mailbox_id: getGlobalAttr("mailbox_id"),
          conversation_id: getGlobalAttr("conversation_id"),
        },
        laroute.route("sidebarwebhook.ajax"),
        function (response) {
          if (isAjaxSuccess(response)) {
            const data = JSON.parse(response.data || "{}");
            resolve(data);
          } else {
            ajaxFinish();
            showAjaxError(response);
            reject();
          }
        },
        true
      );
    });
  }

  function getSearchResults(searchTerm) {
    fsAjax(
      {
        action: "searchClient",
        search: searchTerm,
        mailbox_id: getGlobalAttr("mailbox_id"),
        conversation_id: getGlobalAttr("conversation_id"),
      },
      laroute.route("sidebarwebhook.ajax"),
      function (response) {
        if (
          typeof response.status != "undefined" &&
          response.status == "success" &&
          typeof response.data != "undefined" &&
          response.data
        ) {
          const data = JSON.parse(response.data || "{}");
          user = response.user || {};
          updateResults(data.clients);
        } else {
          showAjaxError(response);
        }
      },
      true
    );
  }

  function updateResults(newClients) {
    clients = newClients;
    renderResults();
  }

  function renderResults() {
    clientResults.empty();

    if (clients.length > 0) {
      $("#new-client-button").addClass("hide");
      clientResults.append(
        $("<h4>", {
          class: "",
          text: `Clients`,
        })
      );
      $.each(clients, function (index, client) {
        const clientItem = $("<div>", {
          class: "search-result-item search-item",
          "data-id": "client" + client.id,
          click: function () {
            $(".search-result-item").removeClass("selected-result");
            $(this).addClass("selected-result");
            displayClientContactForm(client);
          },
        });
        clientItem.append(
          $("<span>", {
            class: "",
            text: `${client.name}, ${client.county}, ${client.state_abb}`,
          })
        );
        clientResults.append(clientItem);
        globalSearchResults.show();
      });
    } else {
      clientResults.append(
        $("<h4>", {
          class: "",
          text: `Client not found. Click the "Add New Client" button to add client`,
        })
      );
      globalSearchResults.show();
      $("#new-client-button").html(
        $("<button>", {
          class: "btn btn-primary",
          text: `Add New Client`,
          click: function () {
            window.open(
              `https://mc2.townweb.com/clients?action=new_client&name=${searchTerm}`,
              "_blank"
            );
          },
        })
      );
      $("#new-client-button").removeClass("hide");
    }
  }

  function clearResults() {
    clientResults.empty();
    globalSearchResults.hide();
    $("#new-client-button").addClass("hide");
    $("#new-client-contact").addClass("hide");
    $("#add-client-contact-button").addClass("hide");
    clients = [];
  }

  function displayClientContactForm(client) {
    $("#new-client-contact").removeClass("hide");
    $("#add-client-contact-button").removeClass("hide");
    if (!$("#email").val()) $("#email").val(user.email);
    if (!$("#phone").val()) $("#phone").val(user.phone);
    selectedClient = client;
  }
});
