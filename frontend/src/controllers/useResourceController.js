import { useCallback, useEffect, useState } from 'react';
import { ApiError, unwrapPaginated } from '../models/ApiClient.js';

export function useResourceController(model, { autoLoad = true } = {}) {
  const [items, setItems] = useState([]);
  const [page, setPage] = useState(1);
  const [meta, setMeta] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [fieldErrors, setFieldErrors] = useState(null);

  const load = useCallback(async (pageNumber = 1, filters = {}) => {
    setLoading(true);
    setError(null);
    try {
      const payload = await model.list(pageNumber, filters);
      const { items: list, meta: pagination } = unwrapPaginated(payload);
      setItems(list);
      setMeta(pagination);
      setPage(pageNumber);
    } catch (err) {
      setError(err instanceof ApiError ? err.message : 'Failed to load data.');
    } finally {
      setLoading(false);
    }
  }, [model]);

  useEffect(() => {
    if (autoLoad) {
      load(1);
    }
  }, [autoLoad, load]);

  const save = useCallback(
    async (data, editingId = null) => {
      setFieldErrors(null);
      setError(null);
      try {
        if (editingId) {
          await model.update(editingId, data);
        } else {
          await model.create(data);
        }
        await load(page);
        return true;
      } catch (err) {
        if (err instanceof ApiError) {
          setError(err.message);
          setFieldErrors(err.errors);
        } else {
          setError('Save failed.');
        }
        return false;
      }
    },
    [model, load, page],
  );

  const remove = useCallback(
    async (id) => {
      setError(null);
      try {
        await model.remove(id);
        await load(page);
        return true;
      } catch (err) {
        setError(err instanceof ApiError ? err.message : 'Delete failed.');
        return false;
      }
    },
    [model, load, page],
  );

  return {
    items,
    page,
    meta,
    loading,
    error,
    fieldErrors,
    load,
    save,
    remove,
    setPage,
  };
}
