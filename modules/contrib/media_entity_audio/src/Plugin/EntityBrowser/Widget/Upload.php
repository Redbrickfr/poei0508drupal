<?php

namespace Drupal\media_entity_audio\Plugin\EntityBrowser\Widget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\entity_browser\Plugin\EntityBrowser\Widget\Upload as FileUpload;
use Drupal\media_entity\MediaInterface;
use Drupal\Core\Url;

/**
 * Uses upload to create media entity audios.
 *
 * @EntityBrowserWidget(
 *   id = "media_entity_audio_upload",
 *   label = @Translation("Upload audio files"),
 *   description = @Translation("Upload widget that creates media entity audios.")
 * )
 */
class Upload extends FileUpload {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'extensions' => 'mp3 wav ogg',
      'media bundle' => NULL,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(array &$original_form, FormStateInterface $form_state, array $aditional_widget_parameters) {
    /** @var \Drupal\media_entity\MediaBundleInterface $bundle */
    if (!$this->configuration['media bundle'] || !($bundle = $this->entityTypeManager->getStorage('media_bundle')->load($this->configuration['media bundle']))) {
      return ['#markup' => $this->t('The media bundle is not configured correctly.')];
    }

    if ($bundle->getType()->getPluginId() != 'audio') {
      return ['#markup' => $this->t('The configured bundle is not using audio plugin.')];
    }

    $form = parent::getForm($original_form, $form_state, $aditional_widget_parameters);
    $form['upload']['#upload_validators']['file_validate_extensions'] = [$this->configuration['extensions']];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareEntities(array $form, FormStateInterface $form_state) {
    $files = parent::prepareEntities($form, $form_state);

    /** @var \Drupal\media_entity\MediaBundleInterface $bundle */
    $bundle = $this->entityTypeManager
      ->getStorage('media_bundle')
      ->load($this->configuration['media bundle']);

    $audios = [];
    foreach ($files as $file) {
      /** @var \Drupal\media_entity\MediaInterface $audio */
      $audio = $this->entityTypeManager->getStorage('media')->create([
        'bundle' => $bundle->id(),
        $bundle->getTypeConfiguration()['source_field'] => $file,
      ]);

      $audios[] = $audio;
    }

    return $audios;
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array &$element, array &$form, FormStateInterface $form_state) {
    if (!empty($form_state->getTriggeringElement()['#eb_widget_main_submit'])) {
      $audios = $this->prepareEntities($form, $form_state);
      array_walk($audios, function (MediaInterface $media) {
        $media->save();
      });

      $this->selectEntities($audios, $form_state);
      $this->clearFormValues($element, $form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $bundle_options = [];
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['extensions'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Allowed extensions'),
      '#default_value' => $this->configuration['extensions'],
      '#required' => TRUE,
    ];

    $bundles = $this->entityTypeManager
      ->getStorage('media_bundle')
      ->loadByProperties(['type' => 'audio']);

    /** @var \Drupal\media_entity\MediaBundleInterface $bundle */
    foreach ($bundles as $bundle) {
      $bundle_options[$bundle->id()] = $bundle->label();
    }

    if (empty($bundle_options)) {
      $url = Url::fromRoute('media.bundle_add')->toString();
      $form['media bundle'] = [
        '#markup' => $this->t("You don't have media bundle of the Audio type. You should <a href='!link'>create one</a>", ['!link' => $url]),
      ];
    }
    else {
      $form['media bundle'] = [
        '#type' => 'select',
        '#title' => $this->t('Media bundle'),
        '#default_value' => $this->configuration['media bundle'],
        '#options' => $bundle_options,
      ];
    }

    return $form;
  }

}
