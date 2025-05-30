export function useVisitWebsite() {
  const visitCompanyWebsite = (website) => {
    if (website) {
      let url = website;
      if (!/^https?:\/\//i.test(url)) {
        url = 'https://' + url;
      }
      window.open(url, '_blank');
    }
  };

  return { visitCompanyWebsite };
}
