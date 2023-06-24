jQuery(document).ready(function () {

/**
* Returns the source of the window.
* @param name
* @param url url of the page.
*/
  function getParameterByName(name, url) {
    if (!url) {
      url = window.location.href;
    }
    name = name.replace(/[\[\]]/g, "\\$&");
    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
    results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, " "));
  }

  // if(getParameterByName('quoteupHash')!=='' && getParameterByName('_quoteupApprovalRejectionNonce') !== '' && getParameterByName('source') == 'emailApprove')
  // {
  //   jQuery('[name=approvalQuote]').click();
  // }

// when the quote is rejected
//show the reason for rejection div text-area
  if(getParameterByName('source') == 'emailReject') {
    quoteRejectionElement = jQuery(".quote-rejection-reason-div");
    quoteRejectionElement.show('slow');
    quoteRejectionElement.find('.quote-rejection-reason-textbox').focus();
  }

// When reject quote button is clicked
  jQuery(".reject-quote-button").click(function (event) {
    quoteRejectionElement = jQuery(".quote-rejection-reason-div");
    if (! quoteRejectionElement.is(":visible") ) {
      quoteRejectionElement.show('slow');
      jQuery('.quote-rejection-reason-textbox').focus()
    } else {
      quoteRejectionElement.hide('slow');
    }
    event.preventDefault();
  });

})