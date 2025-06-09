export function useVisitWebsite() {
  function formatUrl(url, base) {
    if (!url) return null;
    if (url.startsWith('http://') || url.startsWith('https://')) return url;
    if (url.startsWith('www.')) return 'https://' + url;
    if (base && !url.includes(base)) {
      return `https://${base}/${url.replace(/^\/+/, '')}`;
    }
    return 'https://' + url;
  }

  const visitCompanyWebsite = (website, base = null) => {
    const url = formatUrl(website, base);
    if (url) {
      window.open(url, '_blank');
    }
  };

  return { formatUrl, visitCompanyWebsite };
}
