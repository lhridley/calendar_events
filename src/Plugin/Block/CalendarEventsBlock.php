<?php

namespace  Drupal\calendar_events\Plugin\Block;

use Drupal;
use Drupal\node\Entity\Node;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Datetime;

/**
 * @Block(
 *   id = "Events Calendar Block",
 *   admin_label = @translation("Events Calendar Block"),
 *   category = @translation("Drupal Block"),
 * )
 */


class CalendarEventsBlock extends BlockBase{


    /**
    * {@inheritdoc}
    */
    public function defaultConfiguration() {
        return [
        ] + parent::defaultConfiguration();
    }

  /**
   * {@inheritdoc }
   */

  public function build(){
    $language = Drupal::languageManager()->getCurrentLanguage()->getId();
    dump($this->configuration);
    $output = [
      '#theme' => 'calendar_events',
      '#attached' => [
        'library' => [
                        'calendar_events/calendar_events_library',
                        'core/drupal.dialog.ajax',
        ],
       'drupalSettings' => [
                            'calendar_events' => [
                                        'eventsjson' => $this->getEvents(),
                                        'lang' => $language,
                                        'num_cal' => $this->configuration['calendar_events']['numCal'],
                                        'bg_color' => $this->configuration['calendar_events']['bgColor'],
                                        'bg_color_selected' => $this->configuration['calendar_events']['bgColorSelected'],
                                        'bg_color_today' => $this->configuration['calendar_events']['bgColorToday'],
                                        'bg_color_event' => $this->configuration['calendar_events']['bgColorEvent'],
                                        'color' => $this->configuration['calendar_events']['color'],
                                        'color_event' => $this->configuration['calendar_events']['colorEvent'],
                                        'color_other' => $this->configuration['calendar_events']['colorOther'],
                                        'color_month' => $this->configuration['calendar_events']['colorMonth'],
                                        'text_in_modal' => $this->configuration['calendar_events']['textInModal'],
                                        'text_initial_date' => $this->configuration['calendar_events']['textInitialDate'],
                                        'text_end_date' => $this->configuration['calendar_events']['textEndDate'],
                            ]
       ]
      ],
      '#cache' => [
        'max-age' => 0,
      ]
    ];
    return $output;
  }
   private function getEvents(){
    $query = Drupal::entityTypeManager()->getStorage('node')->getQuery();
    $query->condition('type', 'content_calendar_events');
    $nids = $query->execute();
    $events = [];
    $alias_manager = \Drupal::service('path_alias.manager');

    foreach($nids as $nid){
        $node = Node::load($nid);        
        $path = sprintf('/node/%d', $node->id());
        $alias = $alias_manager->getAliasByPath($path);
        $url = (count($alias) > 0) ? $alias : $path;
                 
        array_push($events, [
            'url' => $url,
            'title' => $node->title->value,
            'body' => $node->body->value,
            'start' => $node->field_start_date_of_the_event->value,
            'end' => $node->field_end_date_of_the_event->value,
            'only' => $node->field_only_day->value,
        ]);
    }
    return json_encode($events);
   
    }

    /**
    * {@inheritdoc}
    */
    public function blockForm($form, FormStateInterface $form_state) {
        $form = parent::blockForm($form, $form_state);
        $config = $this->getConfiguration();

        $theme_options = [
                            1 =>'One Months',
                            2 => 'Two Months',
                            3 => 'Three Months',
        ];
        $text_modal = [
          1 =>'Yes',
          0 => 'No',
        ];
        $form['calendar_events'] = [
          'numCal' => [
              '#type' => 'select',
              '#options' => $theme_options,
              '#title' => $this->t('Choose view'),
              '#description' => $this->t('Choose 1,2,3 views'),
              '#default_value' => (array_key_exists($config['calendar_events']['numCal'], $theme_options)) ? $config['calendar_events']['numCal'] : 1,
              '#weight' => '1',
          ],
          'bgColor' => [
              '#type' => 'textfield',
              '#maxlength' => 7, 
              '#title' => $this->t('Bg color calendar'),
              '#description' => $this->t('Choose hex background color for all calendar'),
              '#default_value' => $this->validate_color($config['calendar_events']['bgColor']) ? $config['calendar_events']['bgColor'] : '#FFFFFF',
              '#weight' => '3',
          ],
          'bgColorEvent' => [
            '#type' => 'textfield',
            '#maxlength' => 7, 
            '#title' => $this->t('BgColor Event Day'),
            '#description' => $this->t('Choose hex background color for event day'),
            '#default_value' => $this->validate_color($config['calendar_events']['bgColorEvent']) ? $config['calendar_events']['bgColorEvent'] : '#00AAE4',
            '#weight' => '3',
          ],
          'bgColorSelected' => [
            '#type' => 'textfield',
            '#maxlength' => 7, 
            '#title' => $this->t('Bg color selected text'),
            '#description' => $this->t('Choose hex background color for selected text'),
            '#default_value' => $this->validate_color($config['calendar_events']['bgColorSelected']) ? $config['calendar_events']['bgColorSelected'] : '',
            '#weight' => '3',
          ],
          'bgColorToday' => [
            '#type' => 'textfield',
            '#maxlength' => 7, 
            '#title' => $this->t('Bg color today'),
            '#description' => $this->t('Choose hex background color for today'),
            '#default_value' => $this->validate_color($config['calendar_events']['bgColorToday']) ? $config['calendar_events']['bgColorToday'] : '',
            '#weight' => '3',
          ],
          'color' => [
            '#type' => 'textfield',
            '#maxlength' => 7,
            '#title' => $this->t('Color days'),
            '#description' => $this->t('Choose hex color for all days'),
            '#default_value' => $this->validate_color($config['calendar_events']['color']) ? $config['calendar_events']['color'] : '#111111',
            '#weight' => '3',
          ],
          'colorEvent' => [
            '#type' => 'textfield',
            '#maxlength' => 7,
            '#title' => $this->t('Color event day'),
            '#description' => $this->t('Choose text color for event day'),
            '#default_value' => $this->validate_color($config['calendar_events']['colorEvent']) ? $config['calendar_events']['colorEvent'] : '#20603D',
            '#weight' => '3',
          ],
          'colorOther' => [
            '#type' => 'textfield',
            '#maxlength' => 7,
            '#title' => $this->t('Color other text'),
            '#description' => $this->t('Choose text color for other text'),
            '#default_value' => $this->validate_color($config['calendar_events']['colorOther']) ? $config['calendar_events']['colorOther'] : '#666666',
            '#weight' => '3',
          ],
          'colorMonth' => [
            '#type' => 'textfield',
            '#maxlength' => 7,
            '#title' => $this->t('Color months text'),
            '#description' => $this->t('Choose text color for months text'),
            '#default_value' => $this->validate_color($config['calendar_events']['colorMonth']) ? $config['calendar_events']['colorMonth'] : '#000000',
            '#weight' => '3',
          ],
          'textInitialDate' => [ 
            '#type' => 'textfield',
            '#maxlength' => 50,
            '#title' => $this->t('Initial date text'),
            '#description' => $this->t('Text with initial date'),
            '#default_value' => (strlen($config['calendar_events']['textInitialDate']) > 3) ? $config['calendar_events']['textInitialDate'] : $this->t('Text with initial date'),
            '#weight' => '2',
          ],
          'textEndDate' => [
            '#type' => 'textfield',
            '#maxlength' => 50,
            '#title' => $this->t('End date text'),
            '#description' => $this->t('Text with End date'),
            '#default_value' => (strlen($config['calendar_events']['textEndDate']) > 3) ? $config['calendar_events']['textEndDate'] : $this->t('Text with end date'),
            '#weight' => '2',
          ],
          'textInModal' => [
            '#type' => 'select',
            '#options' => $text_modal,
            '#title' => $this->t('Text in modal'),
            '#description' => $this->t('Choose text in modal or jump to event content'),
            '#default_value' => (array_key_exists($config['calendar_events']['textInModal'], $text_modal)) ? $config['calendar_events']['textInModal'] : 1,
            '#weight' => '1',
            ],
        ];
        return $form;
    }

    /**
     * @return Boolean
     */
    private function validate_color( $str_color ){
      $str_len = strlen($str_color);
      $first   = $str_color[0];
      return ($str_len <= 7 && $str_len > 3 && $first == '#') ? true : false;

    }

    /**
    * {@inheritdoc}
    */
    public function blockSubmit($form, FormStateInterface $form_state) {
        parent::blockSubmit($form, $form_state);
        $values = $form_state->getValues();
        $this->configuration['calendar_events'] = $values['calendar_events'];
    }


}