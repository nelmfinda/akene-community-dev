'use strict';
/**
 * Displays the file path to upload
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(['underscore', 'oro/translator', 'pim/form', 'pim/template/import/file-path'], function (
  _,
  __,
  BaseForm,
  template
) {
  return BaseForm.extend({
    className: 'AknCenteredBox',
    template: _.template(template),

    /**
     * {@inheritdoc}
     */
    initialize: function (config) {
      this.config = config.config;

      BaseForm.prototype.initialize.apply(this, arguments);
    },

    /**
     * {@inheritdoc}
     */
    render: function () {
      const {configuration} = this.getFormData();
      const isSftp = 'sftp' === configuration.storage?.type;

      this.$el.html(
        this.template({
          path: isSftp ? configuration.storage.host : configuration.filePath,
          label: __(isSftp ? 'pim_import_export.form.job_instance.storage_form.host.label' : this.config.label),
        })
      );

      this.delegateEvents();

      return BaseForm.prototype.render.apply(this, arguments);
    },
  });
});
