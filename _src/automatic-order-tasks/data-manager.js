import api from './ajax-api.js';
import * as tasks from './tasks.js';

const dataManager = ($) => {
  let dirty = false;

  return {
    load: () => {
      return new Promise((resolve, reject) => {
        const statusID = $('#order-tasks-container').data('id');

        api.getOrderManagementConfig(statusID)
        .then(data => {

          //remove spinner
          $('.loader').remove();

          if (Array.isArray(data) && data.length) { // add tasks
            for (const taskConfig of data) {
              const task = tasks.orderTaskFactory.get(taskConfig.id);
              task.createFields(taskConfig.fields);
            }
          } else { // show placeholder text
            $('.notask-placeholder').show();
          }
          dirty = false; // all the settings have just been loaded, so dirty is false.
          resolve();
        })
        .catch(err => {
          reject(err);
        })
      })
    },

    save: () => {
      return new Promise((resolve, reject) => {

      // get task manager container
      const $taskManager = $('#order-tasks-container');
      const $tasks = $taskManager.find('.eam-task');
      const configArr = [];
 
      $tasks.each(function() {
        const $task = $(this);

        const taskId = $task.data('id');
        const $fields = $task.find('.eam-field');

        const fieldVals = {};
        $fields.each(function() {
          const $field = $(this);
          
          const fieldId = $field.data('id');
          fieldVals[fieldId] = $field.data('meta').getValue() ;
        });
        configArr.push({ id: taskId, fields: fieldVals });

      });

      const configJSON = JSON.stringify({ orderStatus: $taskManager.data('id'), config: configArr  });

      api.saveOrderStatusConfig(configJSON)
          .then(data => {
            dirty = false;
            resolve(data);
          })
          .catch(err => {
            reject(err);
          });
      });
    },

    setDirty: () => {
      dirty = true;
    },

    isDirty: () => {
      return dirty;
    }
  }
};


const instance = dataManager(jQuery);
export default instance;