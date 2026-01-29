<?php

namespace ProcessWire;

/**
 * @author Bernhard Baumrock, 08.09.2024
 * @license COMMERCIAL DO NOT DISTRIBUTE
 * @link https://www.baumrock.com
 */
class ProcessRockCalendar extends Process
{
  public function init()
  {
    parent::init(); // always remember to call the parent init
  }

  /**
   *
   */
  public function executeTrash()
  {
    $this->headline(' ');
    $this->browserTitle('Trash Event');
    /** @var InputfieldForm $form */
    $form = $this->wire->modules->get('InputfieldForm');

    // the event being trashed
    $p = wire()->pages->get(
      wire()->input->post('id') ?: wire()->input->get('id')
    );

    // check if page is valid
    if (!$p->hasField(RockCalendar::field_date)) {
      $form->add([
        'type' => 'markup',
        'label' => 'Error',
        'value' => 'Invalid page',
        'icon' => 'warning',
      ]);
      return $form->render();
    }
    if (!$p->editable()) {
      $form->add([
        'type' => 'markup',
        'label' => 'Error',
        'value' => 'Page not editable',
        'icon' => 'warning',
      ]);
      return $form->render();
    }

    // add JS file
    $url = wire()->config->urls->siteModules;
    wire()->config->scripts->add($url . 'RockGrid/RockGrid.js');

    // get events to trash
    $type = wire()->input->get('type', ['following', 'all']);
    $all = rockcalendar()->getEventsOfSeries(
      $p,
      $type,
      // we need to include the main event because it's already trashed
      // at this point and if it's not included, the SSE will mess up.
      true,
    );
    $num = count($all);

    // show progress bar and delete all pages
    $wire = wire();
    $form->add([
      'type' => 'markup',
      'label' => 'Progress',
      'icon' => 'trash',
      'description' => 'Please wait while we delete the following ' . $num . ' events of this series...',
      'value' => "<div class='uk-flex uk-flex-middle' style='gap:10px'>
        <span class='uk-text-nowrap'><span id='current'>0</span> / " . $num . "</span>
        <progress max='100' value='1' class='uk-progress uk-margin-remove'></progress>
        </div>
        <script>
        RockGrid.sse({
          url: ProcessWire.config.urls.root + 'rockcalendar/trash-events/',
          data: {
            pid: {$p->id},
            type: '{$type}',
          },
          onMessage: (msg) => {
            const data = JSON.parse(msg);
            console.log(data);
            document.querySelector('#current').innerText = data.current;
            document.querySelector('progress').value = data.progress * 100;
          },
          onDone: () => {
            if(!!{$wire->input->get('modal', 'int')}) {
              // jump out of iframe
              // find closest .ui-dialog
              // click on .ui-dialog-titlebar-close
              const dialog = window.parent.document.querySelector('.ui-dialog');
              const closeButton = dialog.querySelector('.ui-dialog-titlebar-close');
              closeButton.click();
            } else {
              window.location.href = '{$wire->pages->get(2)->url}';
            }
          },
        });
        </script>
      ",
    ]);

    return $form->render();
  }
}
