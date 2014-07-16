(function () {
  'use strict';

  //When document is ready we need to listen for the form submit event.
  document.ready(listenForSubmit);

  function listenForSubmit() {

    /*
     * Get this forms dom object. It is highly unlikely
     * this will ever be undefined but make certain anyway
     */
    var form = typeof JotForm.forms[0] === 'undefined'
      ? $$('.jotform-form')[0] //Find form DOM object
      : JotForm.forms[0]; //Use current JotForm form object

    //Observe the form so we can insert the Formisimo data when it is submitted.
    return Event.observe(form, 'submit', insertFormisimoData.bind(this, form));
  }

  /*
   * The function triggered when the form is submitted.
   * This will create the Formisimo data and insert
   * it into the form.
   */
  function insertFormisimoData(form) {

    //Create the required Formisimo data
    var data = createFormisimoData();

    /*
     * If for any reason there is no Formisimo cookie
     * we can finish execution as we cannot match a
     * conversion to without the cookie
     */
    if ( ! data.cookie) {
      return false;
    }

    /*
     * Create a hidden input to pass the data through
     * the form submission, allowing us to retrieve it
     * on the backend and pass it along to Formisimo
     */
    var input = new Element('input', {
      type: "hidden",
      name: "formisimo-tracking",
      value: Object.toJSON(data) //JSON stringify the data so we can correctly pass it as the input value
    });

    //Finally insert the input onto the form and allow submission to proceed
    return form.insert(input);
  }

  /*
   * Function to create an object of the data not available
   * on our backend webhook request but required by
   * Formsimo to match the form events to the conversion.
   */
  function createFormisimoData() {
    return {
      cookie: getFormisimoCookie(), //The Formisimo cookie containing the session ID
      browsertime: new Date().getTime(), //Browser Time
      timezone: jstz.determine().name() //Browser Timezone. We can use the jstz variable export from the Formisimo tracking file
    };
  }

  /*
   * Function to retrieve the Formisimo cookie.
   */
  function getFormisimoCookie() {
    return document.readCookie('formisimo');
  }
})();