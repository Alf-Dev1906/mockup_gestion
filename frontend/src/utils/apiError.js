export function firstApiError(err, fallback = 'No se pudo completar la operación') {
  const data = err?.response?.data
  if (data?.errors) {
    const first = Object.values(data.errors)[0]
    return Array.isArray(first) ? first[0] : String(first)
  }
  return data?.message || data?.error || fallback
}
