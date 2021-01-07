import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {renderHook} from '@testing-library/react-hooks';
import {useJobExecution} from '../../../../../../Resources/public/js/job/execution/hook/use-job-execution';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge/src';
import {act} from 'react-test-renderer';

declare global {
  namespace NodeJS {
    interface Global {
      fetch: any;
    }
  }
}

afterEach(() => {
  global.fetch && global.fetch.mockClear();
  delete global.fetch;
});

const wrapper = ({children}) => <DependenciesProvider>{children}</DependenciesProvider>;
const successResponse = {
  jobInstance: {
    code: 'csv_product_export',
    label: 'Demo CSV product export',
    type: 'export',
  },
  isStoppable: false,
  tracking: {
    error: false,
    warning: false,
    status: 'STARTED',
    currentStep: 1,
    totalSteps: 1,
    steps: [
      {
        jobName: 'csv_product_export',
        stepName: 'export',
        status: 'STARTED',
        isTrackable: true,
        hasWarning: false,
        hasError: false,
        duration: 14,
        processedItems: 30,
        totalItems: 135,
      },
    ],
  },
  meta: {
    id: '24',
    logExists: false,
    archives: {
      output: {
        label: 'pim_enrich.entity.job_execution.module.download.output',
        files: {
          'export_Demo_CSV_product_export_2021-01-05_10-33-34.csv':
            'export/csv_product_export/24/output/export_Demo_CSV_product_export_2021-01-05_10-33-34.csv',
        },
      },
      archive: {
        label: 'pim_enrich.entity.job_execution.module.download.archive',
        files: {
          'export_Demo_CSV_product_export_2021-01-05_10-33-34.zip':
            'export/csv_product_export/24/archive/export_Demo_CSV_product_export_2021-01-05_10-33-34.zip',
        },
      },
    },
  },
};

test('It returns the fetched job execution', async () => {
  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: true,
    json: async () => successResponse,
  }));

  const {result, waitForNextUpdate} = renderHook(() => useJobExecution('1'), {wrapper});
  await act(async () => {
    await waitForNextUpdate;
  });

  const {jobExecution, error, reloadJobExecution} = result.current;
  expect(jobExecution).toEqual(successResponse);
  expect(error).toBeNull();
  expect(reloadJobExecution).not.toBeNull();
});

test('It returns error when fetch return an error', async () => {
  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: false,
    statusText: 'Not found',
    status: 404,
  }));

  const {result, waitForNextUpdate} = renderHook(() => useJobExecution('1'), {wrapper});
  await act(async () => {
    await waitForNextUpdate;
  });

  const {jobExecution, error, reloadJobExecution} = result.current;
  expect(jobExecution).toBeNull();
  expect(reloadJobExecution).not.toBeNull();
  expect(error).toEqual({
    statusMessage: 'Not found',
    statusCode: 404,
  });
});

test('It returns callback to reload job execution information', async () => {
  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: true,
    json: async () => successResponse,
  }));

  const {result, waitForNextUpdate} = renderHook(() => useJobExecution('1'), {wrapper});
  await act(async () => {
    await waitForNextUpdate;
  });

  const {jobExecution, error, reloadJobExecution} = result.current;
  expect(jobExecution).toEqual(successResponse);
  expect(error).toBeNull();

  const reloadedResponse = {
    ...successResponse,
    ...{
      tracking: {
        error: false,
        warning: false,
        status: 'STARTED',
        currentStep: 1,
        totalSteps: 1,
        steps: [
          {
            jobName: 'csv_product_export',
            stepName: 'export',
            status: 'STARTED',
            isTrackable: true,
            hasWarning: false,
            hasError: false,
            duration: 19,
            processedItems: 115,
            totalItems: 135,
          },
        ],
      },
    },
  };

  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: true,
    json: async () => reloadedResponse,
  }));

  await act(async () => {
    await reloadJobExecution();
  });

  const {jobExecution: newJobExecution, error: newError} = result.current;

  expect(newError).toBeNull();
  expect(newJobExecution).toEqual(reloadedResponse);
});
