<?php
/*
* Super awesome sitemap creator coded in ~5 hour.
*
*/

class SitemapCreator
{
	private $xml, $urlset, $urlList, $host, $protocol, $remainingCount;

	public function __construct()
	{
		//initialize xml element
		$this->xml = new SimpleXMLElement('<xml/>');
		$this->xml->addAttribute('version', '1.0');
		$this->xml->addAttribute('encoding', 'UTF-8');

		// set minimal sitemap standarts
		$this->urlset = $this->xml->addChild('urlset');
		$this->urlset->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

		$this->urlList = [];
	}

	/**
	 * Creates sitemap and returns it.
	 * Returns false if creation failed.
	 *
	 * @todo better error handling
	 *
	 * @param string url
	 * @param int count
	 * @return XML
	 */
	public function Create($url, $count)
	{	
		if (!$this->ValidateUrl($url))
			return false;

		$parsed = parse_url($url);

		// given url most be main page of domain
		if (isset($parsed['path']) && $parsed['path'] !== '/')
			return false;

		$this->host = $parsed['host'];
		$this->protocol = $parsed['scheme'] . '://';
		$this->remainingCount = $count;
		
		//start creation of sitemap starting from $url
		$this->AddUrls($url, 1.0);

		return $this->xml->asXML();
	}

	/**
	 * Adds all urls in current page and calls itself for same domain urls.
	 * @param string currentUrl
	 * @param int remainingCount
	 * @param float priority
	 * @return XML
	 */
	private function AddUrls($currentUrl, $priority)
	{	
		$newUrls = $this->ParseUrls($currentUrl);

		//no new urls for current page
		if (count($newUrls) === 0)
			return;

		foreach ($newUrls as $url) {
			if (!$this->ValidateUrl($url))
				continue;

			//if current url is already added to sitemap, skip it
			if (in_array($url, $this->urlList))
				continue;

			array_push($this->urlList, $url);
			$urlNode = $this->urlset->addChild('url');
			$urlNode->addChild('loc', $url);

			// TODO: add lastmod and changefreq
			// $urlNode->addChild('lastmod', $lastmod);
			// $urlNode->addChild('changefreq', $changefreq);
			$urlNode->addChild('priority', $priority);
			$this->remainingCount--;

			if ($this->remainingCount === 0)
				return;
		}

		foreach ($newUrls as $url) {
			if ($this->remainingCount === 0)
				return;

			// reduce priority by 10% for each recursive level
			// it helps tracing recursive levels
			$this->AddUrls($url, $priority * 0.9);
		}

	}

	/**
	 * Parses all urls in given url and returns new urls in same domain
	 * that we havent added to sitemap yet
	 * @param string url Source url
	 * @return string[]
	 */
	private function ParseUrls($url)
	{
		$urls = [];

		$htmlContent = @file_get_contents($url);
		// die(var_dump($content));

		//handle 404 errors
		if (!$htmlContent)
			return $urls;
	
		$dom = new DOMDocument;
		@$dom->loadHTML($htmlContent);
		$links = $dom->getElementsByTagName('a');

		foreach ($links as $link){
			$href = $link->getAttribute('href');
			$parsed = parse_url($href);
			// var_dump($parsed);

			//link is not a actually link. <a href="javascript:void(0)">
			if (isset($parsed['scheme']) && $parsed['scheme'] === 'javascript')
				continue;

			//link is not in same domain.
			if (isset($parsed['host']) && $parsed['host'] !== $this->host )
				continue;

			//link doesn't have host
			if (!isset($parsed['host'])){

				//TODO: handle urls better.

				if ($href[0] === '.') {
					// <a href="./hey">
					$href = substr($href, 1);
				}

				if ($href[0] !== '/') {
					// <a href="hey">
					$href = '/' . $href;
				}

				$href = $this->protocol . $this->host . $href;
			}
			
			//did we added this url to sitemap before?
			// is this url exists more than once in current page?
			if (in_array($href, $this->urlList) || in_array($href, $urls))
				continue;

			// checking headers of every url is not a good idea.
			// it slow downs whole process a lot.
			// $headers = get_headers($href, 1);

			// //broken link
			// if ( strpos( $headers[0], '200 OK') === false)
			// 	continue;

			// //link is not a page
			// if (strpos( $headers['Content-Type'], 'text/html') === false)
			// 	continue;

			// die(var_dump($headers));

			array_push($urls, $href);
		}
		return $urls;
	}

	/**
	 *
	 * @param string url
	 * @return bool
	 */
	private function ValidateUrl($url)
	{
		//TODO: check if url is a page (not .png, .jpg etc.)
		return filter_var($url, FILTER_VALIDATE_URL);
	}

	/**
	 * @todo: save to an xml file
	 *
	 * @return bool
	 */
	private function Save()
	{
		return false;
	}

}
