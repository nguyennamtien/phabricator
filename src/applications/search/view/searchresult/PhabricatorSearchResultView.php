<?php

/*
 * Copyright 2011 Facebook, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

final class PhabricatorSearchResultView extends AphrontView {

  private $handle;
  private $query;
  private $object;

  public function setHandle(PhabricatorObjectHandle $handle) {
    $this->handle = $handle;
    return $this;
  }

  public function setQuery(PhabricatorSearchQuery $query) {
    $this->query = $query;
    return $this;
  }

  public function setObject($object) {
    $this->object = $object;
    return $this;
  }

  public function render() {
    $handle = $this->handle;

    $type_name = nonempty($handle->getTypeName(), 'Document');

    require_celerity_resource('phabricator-search-results-css');

    $link = phutil_render_tag(
      'a',
      array(
        'href' => $handle->getURI(),
      ),
      PhabricatorEnv::getProductionURI($handle->getURI()));

    switch ($handle->getType()) {
      case PhabricatorPHIDConstants::PHID_TYPE_USER:
        if ($this->object) {
          $img_phid = $this->object->getProfileImagePHID();
          $img = PhabricatorFileURI::getViewURIForPHID($img_phid);
        }
        break;
      default:
        $img = null;
        break;
    }

    if ($img) {
      $img = phutil_render_tag(
        'div',
        array(
          'class' => 'result-image',
          'style' => "background-image: url('{$img}');",
        ),
        '');
    }

    switch ($handle->getType()) {
      case PhabricatorPHIDConstants::PHID_TYPE_CMIT:
        $object_name = $handle->getName();
        if ($this->object) {
          $data = $this->object->getCommitData();
          $summary = $data->getSummary();
          if (strlen($summary)) {
            $object_name = $handle->getName().': '.$data->getSummary();
          }
        }
        break;
      default:
        $object_name = $handle->getFullName();
        break;
    }

    return
      '<div class="phabricator-search-result">'.
        $img.
        '<div class="result-desc">'.
          phutil_render_tag(
            'a',
            array(
              'class' => 'result-name',
              'href' => $handle->getURI(),
            ),
            $this->emboldenQuery($object_name)).
          '<div class="result-type">'.$type_name.' &middot; '.$link.'</div>'.
        '</div>'.
        '<div style="clear: both;"></div>'.
      '</div>';
  }

  private function emboldenQuery($str) {
    if (!$this->query) {
      return phutil_escape_html($str);
    }

    $query = $this->query->getQuery();

    $query = preg_split("/\s+/", $query);
    $query = array_filter($query);
    $str = phutil_escape_html($str);
    foreach ($query as $word) {
      $word = phutil_escape_html($word);
      $str = preg_replace(
        '/(?:^|\b)('.preg_quote($word, '/').')(?:\b|$)/i',
        '<strong>\1</strong>',
        $str);
    }
    return $str;
  }



}
