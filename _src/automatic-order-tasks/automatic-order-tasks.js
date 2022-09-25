import comp from './components.js';
import * as tasks from './tasks.js';
import dm from './data-manager.js';
import mm from './message-manager.js';

jQuery( $ => {
  const { __ } = wp.i18n;

  const taskList = [
    new tasks.SendMailTask(),
    new tasks.CreatePostTask(),
    new tasks.ChangeShippingTask(),
    new tasks.LogToFileTask(),
    new tasks.CustomOrderFieldTask(),
    new tasks.SendWebhookTask(),
    new tasks.TrashOrderTask()
  ]

  const $orderSelect = $('#eam-order-stage');

  const onNewTask = () => {
    const onTaskSelect = (task) => {
      const $newAccordion = task.createFields();

      $newAccordion.click();
      dm.setDirty();
    }
    comp.createNewTaskWindow(taskList, onTaskSelect);
  }


  const onSaveChanges = function() {
    const $btn = $(this);
    const orig_text = $btn.text();
    $btn.text(__('Saving...', 'aotfw-domain'));
    $btn.prop('disabled', true);

    dm.save()
    .then((data) => {
      mm.displayMessage(data.data.message);
    })
    .catch(err => {
      mm.displayMessage(err.data.message, false);
    })
    .finally(() => {
      setTimeout(() => {
        $btn.text(orig_text);
        $btn.prop('disabled', false);
      }, 1000);

    });
  }

  const onViewLog = function() {
    const $url = $(this).attr('href');

    $.ajax({
      url: $url,
      type: 'HEAD',
      success: function() {
        window.open($url, '_blank');
      },
      error: function() {
        mm.displayMessage(__('No log found. New entries can be written using the "Log To File" task', 'aotfw-domain'), false);
      }
    })

    return false;
  }

  const onOrderStatusClicked = function() {
    $(this).data('last-selected', $(this).find('option:selected'));
  }

  const onOrderStatusChanged = function() {
    const $this = $(this);

    if (dm.isDirty()) {
      if (!confirm(__('Unsaved data will be lost. Proceed?', 'aotfw-domain'))) {
        $this.data('last-selected').prop('selected', true);
        return;
      }
    }
    const comps = comp.createBaseComponents($orderSelect);

    comps.newTaskBtn.on('click', onNewTask);
    comps.saveChangesBtn.on('click', onSaveChanges);
    comps.viewLogLink.on('click', onViewLog);

    // load new
    dm.load();
  }

  $orderSelect.on('click', onOrderStatusClicked)
  $orderSelect.on('change', onOrderStatusChanged);
  onOrderStatusClicked();
  onOrderStatusChanged(); 

} ); 