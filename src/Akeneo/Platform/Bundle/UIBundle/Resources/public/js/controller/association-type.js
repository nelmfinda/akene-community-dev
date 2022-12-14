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
      return FetcherRegistry.getFetcher('association-type')
        .fetch(route.params.code, {cached: false})
        .then(associationType => {
          if (!this.active) {
            return;
          }

          PageTitle.set({
            'association type.label': i18n.getLabel(
              associationType.labels,
              UserContext.get('catalogLocale'),
              associationType.code
            ),
          });

          return FormBuilder.build(associationType.meta.form).then(form => {
            this.on('pim:controller:can-leave', function (event) {
              form.trigger('pim_enrich:form:can-leave', event);
            });
            form.setData(associationType);
            form.trigger('pim_enrich:form:entity:post_fetch', associationType);
            form.setElement(this.$el).render();

            return form;
          });
        });
    },
  });
});
