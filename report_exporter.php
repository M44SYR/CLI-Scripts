<?php
/*
 * Gravity Forms Front-End Export Tool
 *
 * Built to allow authorised users to preview and export selected Gravity Forms
 * data without requiring access to WordPress admin.
 *
 * The main objective was to create a safer and more practical reporting layer
 * for operational users, where access could be controlled by limiting which
 * forms were available to them rather than exposing all form data through the
 * standard admin interface.
 *
 * This was implemented as a front-end shortcode-based tool using a PHP snippet
 * plugin, combining PHP, HTML and CSS to provide:
 *
 * - form-specific access control
 * - date range filtering
 * - field-based filtering
 * - selectable export columns
 * - sortable preview output
 * - CSV export
 *
 * The tool was designed as a pragmatic interim solution to improve reporting
 * access while reducing the risk of users viewing data outside their remit.
 *
 * Implementation included AI-assisted scaffolding for parts of the boilerplate,
 * with the workflow, filtering logic, access approach, and export behaviour
 * adapted to fit the operational requirements of the business.
 */
ob_start();
// Input Form ID and description below to populate the dropdown on the page
$allowed_forms = [
    0 => 'Please Select',
    4 => 'form name 1',
    126 => 'form name 2',
    101 => 'form name 3',
    108 => 'form name 4',
    103 => 'form name 5',
    79 => 'form name 6',
];

$excluded_field_types = ['html', 'section', 'page', 'captcha', 'password'];

$entries = [];
$show_preview = false;
$fields_to_show = [];
$form_id = isset($_POST['form_id']) ? (int) $_POST['form_id'] : null;
$start_date = !empty($_POST['start_date']) ? $_POST['start_date'] . ' 00:00:00' : null;
$end_date = !empty($_POST['end_date']) ? $_POST['end_date'] . ' 23:59:59' : null;
$filter_field = $_POST['filter_field'] ?? '';
$filter_value = $_POST['filter_value'] ?? '';
$selected_fields = $_POST['selected_fields'] ?? [];
$sort_order = $_POST['sort_order'] ?? '';

function get_field_by_id($fields, $id) {
    foreach ($fields as $field) {
        if ((string)$field->id === (string)$id) {
            return $field;
        }
    }
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['download']) && $form_id) {
    if (!array_key_exists($form_id, $allowed_forms)) {
        exit('Access denied.');
    }

    $form_meta = GFAPI::get_form($form_id);
    if (!$form_meta) {
        exit('Form not found.');
    }

    $search_criteria = [];
    if ($start_date) $search_criteria['start_date'] = $start_date;
    if ($end_date)   $search_criteria['end_date'] = $end_date;

    // FIXED: correct field_filters shape
    if ($filter_field && $filter_value) {
        $search_criteria['field_filters'] = ['mode' => 'all'];
        $search_criteria['field_filters'][] = [
            'key'      => $filter_field,
            'value'    => $filter_value,
            'operator' => 'is'
        ];
    }

    $paging = ['offset' => 0, 'page_size' => 0];
    $sorting = $sort_order ? ['key' => $sort_order, 'direction' => 'ASC', 'is_numeric' => false] : null;
    $entries = GFAPI::get_entries($form_id, $search_criteria, $sorting, $paging);

    while (ob_get_level()) {
        ob_end_clean();
    }

    $filename = $allowed_forms[$form_id];
    if ($start_date && $end_date) {
        $filename .= "from" . date('Ymd', strtotime($start_date)) . "to" . date('Ymd', strtotime($end_date));
    } elseif ($start_date) {
        $filename .= "from" . date('Ymd', strtotime($start_date));
    } elseif ($end_date) {
        $filename .= "to" . date('Ymd', strtotime($end_date));
    }
    $filename .= ".csv";

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');
    fputs($output, "\xEF\xBB\xBF");

    $headers = ['Entry ID', 'Entry Date'];
    foreach ($form_meta['fields'] as $field) {
        if (!in_array($field->type, $excluded_field_types) && (empty($selected_fields) || in_array((string)$field->id, $selected_fields))) {
            $headers[] = GFFormsModel::get_label($field);
        }
    }
    fputcsv($output, $headers);

    foreach ($entries as $entry) {
        $row = [
            $entry['id'],
            date('Y-m-d H:i:s', strtotime($entry['date_created']))
        ];
        foreach ($form_meta['fields'] as $field) {
            if (!in_array($field->type, $excluded_field_types) && (empty($selected_fields) || in_array((string)$field->id, $selected_fields))) {
                $row[] = $entry[$field->id] ?? '';
            }
        }
        fputcsv($output, $row);
    }

    fclose($output);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_GET['download']) && $form_id) {
    if (array_key_exists($form_id, $allowed_forms)) {
        $search_criteria = [];
        if ($start_date) $search_criteria['start_date'] = $start_date;
        if ($end_date)   $search_criteria['end_date'] = $end_date;

        // FIXED: correct field_filters shape
        if ($filter_field && $filter_value) {
            $search_criteria['field_filters'] = ['mode' => 'all'];
            $search_criteria['field_filters'][] = [
                'key'      => $filter_field,
                'value'    => $filter_value,
                'operator' => 'is'
            ];
        }

        $paging = ['offset' => 0, 'page_size' => 0];
        $sorting = $sort_order ? ['key' => $sort_order, 'direction' => 'ASC', 'is_numeric' => false] : null;
        $entries = GFAPI::get_entries($form_id, $search_criteria, $sorting, $paging);

        $form_meta = GFAPI::get_form($form_id);
        $fields_to_show = $selected_fields ?: [];

        if (empty($fields_to_show)) {
            foreach ($form_meta['fields'] as $field) {
                if (!in_array($field->type, $excluded_field_types)) {
                    $fields_to_show[] = (string) $field->id;
                }
            }
        }

        $show_preview = true;
    }
}
?>

<!-- Styling -->
<style>
  .entry-table {
    width: 100%;
    border-collapse: collapse;
    font-family: Arial, sans-serif;
  }
  .entry-table th, .entry-table td {
    border: 1px solid #ddd;
    padding: 8px;
  }
  .entry-table th {
    background-color: #f2f2f2;
    text-align: left;
  }
  .entry-table tr:nth-child(even) {
    background-color: #f9f9f9;
  }
  .entry-table tr:hover {
    background-color: #e9e9e9;
  }
</style>

<!-- Export Form UI -->
<form method="POST" style="margin-bottom:10px;">
  <label for="form_id">Choose a Report:</label>
  <select name="form_id" id="form_id" required>
    <?php foreach ($allowed_forms as $id => $label): ?>
      <option value="<?= esc_attr($id) ?>" <?= ($form_id == $id) ? 'selected' : '' ?>>
        <?= esc_html($label) ?>
      </option>
    <?php endforeach; ?>
  </select>

  <br>

  <label for="start_date">Start Date:</label>
  <input type="date" name="start_date" id="start_date" value="<?= esc_attr($_POST['start_date'] ?? '') ?>">

  <label for="end_date">End Date:</label>
  <input type="date" name="end_date" id="end_date" value="<?= esc_attr($_POST['end_date'] ?? '') ?>">

  <?php if ($form_id && isset($form_meta)): ?>
    <br><br>

    <label for="filter_field">Filter Field:</label>
    <select name="filter_field" id="filter_field">
      <option value="">-- Select Field --</option>
      <?php foreach ($form_meta['fields'] as $field): 
        if (!in_array($field->type, $excluded_field_types)): ?>
          <option value="<?= esc_attr($field->id) ?>" <?= ($filter_field == $field->id) ? 'selected' : '' ?>>
            <?= esc_html(GFFormsModel::get_label($field)) ?>
          </option>
      <?php endif; endforeach; ?>
    </select>

    <label for="filter_value">Filter Value:</label>
    <input type="text" name="filter_value" id="filter_value" value="<?= esc_attr($filter_value) ?>">

    <br><br>

 <fieldset style="max-height: 200px; overflow-y: auto; border: 1px solid #ccc; padding: 10px;">
<legend style="width: auto; padding: 0 8px; font-weight: light;">Select Fields to Display:</legend>
  <?php foreach ($form_meta['fields'] as $field): 
    if (!in_array($field->type, $excluded_field_types)):
      $checked = in_array((string)$field->id, $selected_fields) ? 'checked' : '';
  ?>
    <label style="display: block; margin-bottom: 4px;">
      <input type="checkbox" name="selected_fields[]" value="<?= esc_attr($field->id) ?>" <?= $checked ?>>
      <?= esc_html(GFFormsModel::get_label($field)) ?>
    </label>
  <?php endif; endforeach; ?>
</fieldset>
<br>
    <label for="sort_order">Sort By:</label>
    <select name="sort_order">
      <option value="">-- None --</option>
      <?php foreach ($form_meta['fields'] as $field): 
        if (!in_array($field->type, $excluded_field_types)): ?>
          <option value="<?= esc_attr($field->id) ?>" <?= ($sort_order == $field->id) ? 'selected' : '' ?>>
            <?= esc_html(GFFormsModel::get_label($field)) ?>
          </option>
      <?php endif; endforeach; ?>
    </select>
  <?php endif; ?>
<br>
  <button type="submit" style="padding: 8px 16px; font-size: 16px; cursor: pointer;">Update Preview</button>
</form>

<?php if ($form_id): ?>
  <form method="POST" action="<?= esc_url(add_query_arg('download', '1')) ?>" style="margin-bottom:20px;">
    <input type="hidden" name="form_id" value="<?= esc_attr($form_id) ?>">
    <input type="hidden" name="start_date" value="<?= esc_attr($_POST['start_date'] ?? '') ?>">
    <input type="hidden" name="end_date" value="<?= esc_attr($_POST['end_date'] ?? '') ?>">
    <input type="hidden" name="filter_field" value="<?= esc_attr($filter_field) ?>">
    <input type="hidden" name="filter_value" value="<?= esc_attr($filter_value) ?>">
    <?php foreach ($selected_fields as $field_id): ?>
      <input type="hidden" name="selected_fields[]" value="<?= esc_attr($field_id) ?>">
    <?php endforeach; ?>
    <input type="hidden" name="sort_order" value="<?= esc_attr($sort_order) ?>">
    <button type="submit" style="padding: 8px 16px; font-size: 16px; cursor: pointer;">Download</button>
  </form>
<?php endif; ?>

<?php if ($show_preview): ?>
  <h3>Entry Preview</h3>
  <?php if (empty($entries)): ?>
    <p>No entries found for this date range.</p>
  <?php else: ?>
    <p><strong><?= count($entries) ?> entries shown.</strong></p>
    <div style="max-width: 100%; overflow-x: auto;">
      <table class="entry-table">
        <thead>
          <tr>
            <th>Entry ID</th>
            <th>Entry Date</th>
            <?php foreach ($fields_to_show as $field_id): 
              $field = get_field_by_id($form_meta['fields'], $field_id);
              $label = $field ? GFFormsModel::get_label($field) : 'Unknown Field';
            ?>
              <th><?= htmlspecialchars($label) ?></th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($entries as $entry): ?>
            <tr>
              <td><?= htmlspecialchars($entry['id']) ?></td>
              <td><?= htmlspecialchars(date('Y-m-d H:i:s', strtotime($entry['date_created']))) ?></td>
              <?php foreach ($fields_to_show as $field_id): ?>
                <td><?= htmlspecialchars($entry[$field_id] ?? '') ?></td>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
<?php endif; ?>