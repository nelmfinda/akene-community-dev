import {useEffect, useState} from 'react';
import {fetchKeyIndicators} from '../../fetcher';
import {KeyIndicatorMap} from '../../../domain';

const useFetchKeyIndicators = (
  channel: string,
  locale: string,
  familyCode: string | null,
  categoryCode: string | null
) => {
  const [keyIndicators, setKeyIndicators] = useState<KeyIndicatorMap | null>(null);

  useEffect(() => {
    setKeyIndicators(null);
  }, [channel, locale, familyCode, categoryCode]);

  useEffect(() => {
    (async () => {
      const data = await fetchKeyIndicators(channel, locale, familyCode, categoryCode);
      setKeyIndicators(data);
    })();
  }, [channel, locale, familyCode, categoryCode]);

  return keyIndicators;
};

export {useFetchKeyIndicators};
