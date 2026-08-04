-- Integración Flow para instalaciones existentes.
ALTER TABLE orders
  MODIFY payment_provider VARCHAR(50) DEFAULT 'flow',
  ADD INDEX idx_orders_payment_reference (payment_reference);
