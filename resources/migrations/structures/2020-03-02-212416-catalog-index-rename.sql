ALTER TABLE catalog_order RENAME INDEX session_idx TO order_session_idx;
ALTER TABLE catalog_tracking RENAME INDEX session_idx TO order_session_idx;
