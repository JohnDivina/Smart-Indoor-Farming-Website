'use client';

import { useState, useEffect, useCallback } from 'react';

export function useControlStatus<T>(endpoint: string, intervalMs: number = 4000) {
  const [data, setData] = useState<T | null>(null);
  const [loading, setLoading] = useState<boolean>(true);
  const [error, setError] = useState<string | null>(null);

  const fetchStatus = useCallback(async () => {
    try {
      const res = await fetch(endpoint);
      if (!res.ok) throw new Error(`Status fetch error ${res.status}`);
      const json = await res.json();
      setData(json);
      setError(null);
    } catch (err: any) {
      setError(err?.message || 'Control fetch error');
    } finally {
      setLoading(false);
    }
  }, [endpoint]);

  useEffect(() => {
    fetchStatus();

    const timer = setInterval(() => {
      if (!document.hidden) {
        fetchStatus();
      }
    }, intervalMs);

    return () => clearInterval(timer);
  }, [fetchStatus, intervalMs]);

  return { data, loading, error, refetch: fetchStatus, setData };
}

export default useControlStatus;
