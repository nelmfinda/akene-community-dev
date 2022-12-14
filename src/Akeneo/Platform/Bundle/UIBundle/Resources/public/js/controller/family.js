'use strict';

define([
  'oro/translator',
  'pim/controller/front',
  'pim/form-builder',
  'pim/fetcher-registry',
  'pim/user-context',
  'pim/dialog',
  'pim/page-title',
  'pim/i18n',
], function (__, BaseController, FormBuilder, FetcherRegistry, UserContext, Dialog, PageTitle, i18n) {
  return BaseController.extend({
    /**
     * {@inheritdoc}
     */
    renderForm: function (route) {
      return FetcherRegistry.getFetcher('family')
        .fetch(route.params.code, {cached: false, full_attributes: false})
        .then(family => {
          if (!this.active) {
            return;
          }

          PageTitle.set({'family.label': i18n.getLabel(family.labels, UserContext.get('catalogLocale'), family.code)});

          return FormBuilder.build(family.meta.form).then(form => {
            this.on('pim:controller:can-leave', function (event) {
              form.trigger('pim_enrich:form:can-leave', event);
            });
            form.setData(family);
            form.trigger('pim_enrich:form:entity:post_fetch', family);
            form.setElement(this.$el).render();

            return form;
          });
        });
    },
  });
});
