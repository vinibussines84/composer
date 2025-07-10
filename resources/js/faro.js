import { getWebInstrumentations, initializeFaro } from '@grafana/faro-web-sdk'
import { TracingInstrumentation } from '@grafana/faro-web-tracing'

initializeFaro({
  url: 'https://faro-collector-prod-sa-east-1.grafana.net/collect/60068c3cdb87b8f16581efb184df7bab',
  app: {
    name: 'Ok',
    version: '1.0.0',
    environment: 'production',
  },
  instrumentations: [
    ...getWebInstrumentations(),
    new TracingInstrumentation(),
  ],
});
