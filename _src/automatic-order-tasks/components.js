const components = ($) => {

  const { __ } = wp.i18n;

  function template_headline(statusName) {
    return `<h2>${__('When an order reaches the status', 'aotfw-domain')}<div class="status-name">${statusName}</div></h2>`;
  }

  function template_tasksContainer(statusValue) {
    return `<div id="order-tasks-container" data-id="${statusValue}">
            <div class="loader"><div class="lds-roller"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div></div>
            <div class="notask-placeholder" style="display: none;">${__('No tasks are set for this order status.<br>Click the button below to add your first one!', 'aotfw-domain')}</div>
            </div>`;
  }

  function template_newTaskButton() {
    return `<button type="button" class="eam-button" id="new-task-btn"><i class="fa-solid fa-plus" style="margin-right: 5px; font-size: .84em"></i>${__('New Task', 'aotfw-domain')}</button>`;
  }

  function template_saveChangesButton() {
    return `<button type="button" class="eam-button" id="save-changes-btn"><i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i>${__('Save Changes', 'aotfw-domain')}</button>`
  }

  return {
    createBaseComponents: ($statusSelect) => {
      const $container = $('#eam-order-options');
      $container.children().remove();

      const $selectedStatus = $statusSelect.find('option:selected');

      // create html elements
      const statusName = $selectedStatus.text();
      $container.append(template_headline(statusName));

      const statusValue = $selectedStatus.val();
      const $orderTasksContainer = $(template_tasksContainer(statusValue));

      $container.append($orderTasksContainer);

      const $newTaskBtn = $(template_newTaskButton());
      $container.append($newTaskBtn);

      const $saveChangesBtn = $(template_saveChangesButton());
      $container.append($saveChangesBtn);

      const $viewLogLink = $('#view-log-link');

      return {
        newTaskBtn: $newTaskBtn,
        saveChangesBtn: $saveChangesBtn,
        orderTasksContainer: $orderTasksContainer,
        viewLogLink: $viewLogLink
      }
    },

    createNewTaskWindow: (tasks, onTaskSelectCallback) => {
      const $taskWindow = $('<div class="new-task-window-overlay"><div class="new-task-window"><div class="grid-container"></div><div class="close-btn"><i class="fa-solid fa-xmark"></i></div></div></div>');

      let taskLinks = [];
      for (const task of tasks) {
        const taskMeta = task.getMeta();

        const $taskGrid = $(
        `<div class="task-selector-unit" id="${taskMeta.id}" data-id="${taskMeta.id}">
          <div class="task-icon"><i class="${taskMeta.icon}"></i></div>
          <div class="task-text">${taskMeta.text}</div>
        </div>`);

        $taskGrid.on('click', function() {
          $taskWindow.remove();
          onTaskSelectCallback(task);
        });

        taskLinks.push($taskGrid);
      }

      //TODO: remove this temporary solution placeholder at a later stage
      for (let i = 0; i<2; i++) {
        const $taskGrid = $(`<div class="task-selector-unit placeholder"></div>`);
        taskLinks.push($taskGrid);
      }
      //END

      const $tasksContainer = $taskWindow.find('.grid-container');
      taskLinks.forEach( x => $tasksContainer.append(x) );

      $taskWindow.find('.close-btn').on('click', function() {
        $taskWindow.remove();
      });

      $('body').append($taskWindow);
    }
  }
}


const instance = components(jQuery);
export default instance;