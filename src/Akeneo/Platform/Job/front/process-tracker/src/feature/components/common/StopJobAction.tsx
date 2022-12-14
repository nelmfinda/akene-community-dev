import React, {SyntheticEvent} from 'react';
import {Button, ButtonProps, ExportIllustration, Helper, Link, Modal, useBooleanState} from 'akeneo-design-system';
import {useRoute, useSecurity, useTranslate} from '@akeneo-pim-community/shared';

type StopJobActionProps = {
  id: string;
  jobLabel: string;
  isStoppable: boolean;
  onStop: () => void;
} & ButtonProps;

const StopJobAction = ({id, jobLabel, isStoppable, onStop, ...rest}: StopJobActionProps) => {
  const translate = useTranslate();
  const {isGranted} = useSecurity();
  const stopRoute = useRoute('pim_enrich_job_tracker_rest_stop', {id});
  const [isConfirmOpen, openConfirm, closeConfirm] = useBooleanState(false);

  const handleStop = async () => {
    closeConfirm();
    await fetch(stopRoute);
    onStop();
  };

  const handleOpenConfirm = (e: SyntheticEvent) => {
    e.stopPropagation();
    e.preventDefault();
    openConfirm();
  };

  if (!isStoppable || !isGranted('pim_importexport_stop_job')) return null;

  return (
    <>
      {isConfirmOpen && (
        <Modal closeTitle={translate('pim_common.close')} onClose={closeConfirm} illustration={<ExportIllustration />}>
          <Modal.SectionTitle color="brand">{translate('pim_title.pim_enrich_job_tracker_index')} /</Modal.SectionTitle>
          <Modal.Title>{translate('pim_datagrid.action.stop.confirmation.title', {jobLabel})}</Modal.Title>
          <Helper level="info">
            {translate('pim_datagrid.action.stop.confirmation.content')}
            <Link
              href="https://help.akeneo.com/pim/serenity/articles/monitor-jobs.html#how-to-stop-your-jobs"
              target="_blank"
            >
              {translate('pim_datagrid.action.stop.confirmation.link')}
            </Link>
          </Helper>
          <Modal.BottomButtons>
            <Button level="tertiary" onClick={closeConfirm}>
              {translate('pim_common.cancel')}
            </Button>
            <Button level="danger" onClick={handleStop}>
              {translate('pim_datagrid.action.stop.confirmation.ok')}
            </Button>
          </Modal.BottomButtons>
        </Modal>
      )}
      <Button onClick={handleOpenConfirm} level="danger" {...rest}>
        {translate('pim_datagrid.action.stop.title')}
      </Button>
    </>
  );
};

export {StopJobAction};
