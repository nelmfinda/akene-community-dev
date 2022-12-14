import {ProductEvaluation} from '../../../domain';
import ProductEvaluationFetcher from './ProductEvaluationFetcher';

const Routing = require('routing');

const ROUTE_NAME = 'akeneo_data_quality_insights_product_evaluation';

const fetchProductDataQualityEvaluation: ProductEvaluationFetcher = async (
  productUuid: string
): Promise<ProductEvaluation> => {
  const response = await fetch(
    Routing.generate(ROUTE_NAME, {
      productUuid,
    })
  );

  return await response.json();
};

export default fetchProductDataQualityEvaluation;
