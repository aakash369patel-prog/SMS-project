import { useCallback, useEffect, useState } from 'react';
import { DashboardModel } from '../models/DashboardModel.js';

export function useDashboardController() {
  const [stats, setStats] = useState(null);
  const [charts, setCharts] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  const load = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const data = await DashboardModel.getStats();
      setStats(data.stats);
      setCharts(data.charts ?? null);
    } catch {
      setError('Could not load dashboard stats.');
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    load();
  }, [load]);

  return { stats, charts, loading, error, reload: load };
}
