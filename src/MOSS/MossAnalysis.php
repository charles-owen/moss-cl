<?php
/**
 * @file
 * Class to attach to a submission to support Moss analysis
 */

namespace CL\MOSS;


use CL\Site\Site;
use CL\Course\Member;
use CL\Course\Analysis\Analyzer;

/**
 * Class to attach to a submission to support Moss analysis
 *
 * This indicates that a submission will be subject to Moss
 * (Measure of Software Similarity) analysis. The analysis
 * is not performed by this component, instead it is configured
 * for a bulk analysis session.
 *
 * Usage example:
~~~~~~~~~~~~~~~~~~~~~{.php}
$moss = $submission->add_analysis(new \CL\Course\Analysis\MossAnalysis());
$moss->type = "zip";
$moss->user = "285435963";
$moss->language = "cc";
$moss->include = "#(BugLib|" .
"CanadianExperience/CanadianExperience).*\\.(cpp|h)$#i";
$moss->exclude = "#(App|MainFrm|stdafx|targetver|XmlNode|" .
"resource|initialize|DoubleBufferDC|BugLib|Basket|BasketStandin|" .
"Bug|BugFactory|BugStandin|BugSeedDlg|PseudoRandom|Head|" .
"Actor|ActorFactory|AnimChannel|AnimChannelAngle|AnimChannelPoint|".
"CanadianExperience|HaroldFactory|ImageDrawable|Picture|" .
"PictureFactory|PictureObserver|PolyDrawable|RotatedBitmap|" .
"SpartyFactory|Timeline|TimelineDlg|ViewEdit|ViewTimeline|" .
"HeadTop|Drawable)\\.(cpp|h)$#i";

// Optional limit on how many to send (use while setting up)
$moss->limit = 3;
~~~~~~~~~~~~~~~~~~~~~
 *
 * @cond
 * @property boolean experimental
 * @property string atLeast
 * @property string language
 * @property string user
 *
 * @endcond
 */
class MossAnalysis extends \CL\Course\Analysis\Analysis {
	/// Tag for this analysis component
	const TAG = 'moss';


	/**
	 * Property get magic method
	 *
	 * <b>Properties</b>
	 * Property | Type | Description
	 * -------- | ---- | -----------
	 * atLeast | string | Minimum member role to perform that analysis (default Member::INSTRUCTOR)
	 * exclude | string | Regular expression that represents files to exclude from analysis
	 * experimental | bool | Set true to use the MOSS experimental server
	 * include | string | Regular expression that represents file to include in analysis
	 * language | string | Language, like 'cc' or other supported MOSS languages
	 * limit | null or int | Limit on number of submissions to upload
	 * type | string | Type of the submission (like 'zip')
	 * user | string | The MOSS user ID, usually a 9-digit number
	 *
	 * @param string $property Property name
	 * @return mixed
	 */
	public function __get($property) {
		switch($property) {
			case 'type':
				return $this->type;

			case 'user':
				return $this->user;

			case 'language':
				return $this->language;

			case 'include':
				return $this->include;

			case 'exclude':
				return $this->exclude;

			case 'limit':
				return $this->limit;

			case 'atLeast':
				return $this->atLeast;

			case 'experimental':
				return $this->experimental;

			default:
				return parent::__get($property);
		}
	}

	/**
	 * Property set magic method
	 *
	 * <b>Properties</b>
	 * Property | Type | Description
	 * -------- | ---- | -----------
	 * atLeast | string | Minimum member role to perform that analysis (default Member::INSTRUCTOR)
	 * exclude | string | Regular expression that represents files to exclude from analysis
	 * include | string | Regular expression that represents file to include in analysis
	 * language | string | Language, like 'cc' or other supported MOSS languages
	 * limit | null or int | Limit on number of submissions to upload
	 * type | string | Type of the submission (like 'zip')
	 * user | string | The MOSS user ID, usually a 9-digit number
	 *
	 * @param string $property Property name
	 * @param mixed $value Value to set
	 */
	public function __set($property, $value) {
		switch($property) {
			case 'type':
				$this->type = $value;
				break;

			case 'user':
				$this->user = $value;
				break;

			case 'language':
				$this->language = $value;
				break;

			case 'include':
				$this->include = $value;
				break;

			case 'exclude':
				$this->exclude = $value;
				break;

			// Setting a limit will only analyze a limited number of assignments
			case 'limit':
				$this->limit = $value;
				break;

			case 'atLeast':
				$this->atLeast = $value;
				break;

			case 'experimental':
				$this->experimental = $value;
				break;

			default:
				parent::__set($property, $value);
				break;
		}
	}



	/**
	 * MOSS does not do per-submission analysis, so this is disabled.
	 * @param Site $site The site object
	 * @param Analyzer $analyzer The analyzer for a submission
	 * @return void
	 * @throws AnalysisException If unable to unzip the solution or execute Doxygen
	 */
	public function analyze(Site $site, Analyzer $analyzer) {
	}

	/**
	 * Get information about the analysis component
	 * @return mixed Array with key 'name'
	 */
	public function info(Site $site) {
		return null;
	}

	/**
	 * Present analysis for the user
	 * @param array $analysis The analysis array as stored with the submission
	 * @return string HTML
	 */
	public function present(array $analysis) {
		return "";
	}

	/**
	 * Get any grading page link for analysis
	 * @return array with keys 'link', 'text', and 'atLeast'
	 */
	public function get_link() {
		$submissionTag = $this->submission->tag;
		$submissionName = $this->submission->name;
		$assignmentTag = $this->submission->assignment->tag;

		return [
			'url'=>"/cl/moss/$assignmentTag/$submissionTag",
			'text'=>"MOSS Analysis for $submissionName",
			'atLeast'=>$this->atLeast
		];
	}

	private $type = "";
	private $user = null;
	private $language = "cc";
	private $include = '#.*#';
	private $exclude = null;
	private $limit = null;
	private $atLeast = Member::INSTRUCTOR;
	private $experimental = false;
}