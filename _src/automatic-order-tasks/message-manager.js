const messageManager = ($) => {

  const successColor = '#57ab57';
  const errorColor = '#e75d5d';

  const secondsOnScreen = 5;
  
  let msgTimeout;

  return {

    displayMessage: (message, success = true) => {
      const $msgBox = $('#aotfw-msg-box');

      $msgBox.text(message);
      $msgBox.css('background-color', success ? successColor : errorColor);
      $msgBox.css('opacity', 1);

      clearTimeout(msgTimeout);
      msgTimeout = setTimeout(() => {
        $msgBox.css('opacity', 0);
      }, secondsOnScreen*1000);
    }

  }
}


const instance = messageManager(jQuery);
export default instance;