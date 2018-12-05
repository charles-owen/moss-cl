<?php
/**
 * @file
 * View class for MOSS analysis
 */

namespace CL\MOSS;


use CL\Site\Site;
use CL\Site\System\Server;
use CL\Course\Members;
use CL\Course\Member;
use CL\Users\User;
use CL\Course\Analysis\Analyzer;

/**
 * View class for MOSS analysis
 */
class MossView extends \CL\Course\View {
	/**
	 * SectionSelectorView constructor.
	 * @param Site $site Site object
	 */
	public function __construct(Site $site, Server $server, array $properties) {
		parent::__construct($site, ['atLeast'=>Member::STAFF]);

		$this->setTitle('MOSS Analysis');

		// Get the assignment
		$assignTag = $properties['assign'];
		$this->assignment = $this->section->get_assignment($assignTag);
		if($this->assignment === null) {
			$server->redirect($site->root . '/cl/invalid');
			return;
		}

		$this->assignment->load();

		// Get the submission
		$submissionTag = $properties['submission'];
		$this->submission = $this->assignment->submissions->get($submissionTag);
		if($this->submission === null) {
			$server->redirect($site->root . '/cl/invalid');
			return;
		}

		// Get the analysis component
		foreach($this->submission->analysis as $analysis) {
			if($analysis instanceof MossAnalysis) {
				$this->moss = $analysis;
				break;
			}
		}

		if($this->moss === null) {
			$server->redirect($site->root . '/cl/invalid');
			return;
		}

		if(!$this->user->atLeast($this->moss->atLeast)) {
			$server->redirect($site->root . '/cl/notauthorized');
			return;
		}

		$this->setTitle('MOSS Analysis for ' . $this->assignment->name . '/' . $this->submission->name);
	}


	/**
	 * Present the section selector
	 * @return string HTML
	 */
	public function present() {
		return '';
	}

	/**
	 * Presentation of a whole page
	 * @return string HTML for the page
	 */
	public function whole() {
		$head = $this->head();
		$header = $this->header();
		$footer = $this->footer();

		while(ob_get_level() > 0) {
			ob_end_flush();
		}

		echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>$head
<style>
div.moss-names p {
	margin: 0;
}
</style>
</head>
<body>$header
<div class="full">
HTML;

		ob_start();
		$this->present_live(function($html) {
			echo $html;
			ob_flush();
			flush();
		});
		ob_end_flush();

		echo <<<HTML
</div>$footer</body>
</html>
HTML;

		return '';
	}

	/**
	 * Present the MOSS results/output
	 *
	 * This is the "live" function, which allows output to the
	 * web result during processing so we you an see progress
	 * rather than waiting until it is done.
	 *
	 * @param $output
	 * @throws \Exception
	 */
	private function present_live($output) {
		if($this->moss === null) {
			$output("<p>No analysis possible</p>");
			return;
		}

		if($this->moss->user === null) {
			$output("<p>MOSS user id not set</p>");
			return;
		}

		$moss = new MOSS($this->moss->user);
		$moss->setLanguage($this->moss->language);
		$moss->setExperimentalServer($this->moss->experimental);

		/**
		 * Pull the submissions
		 */
		$members = new Members($this->site->db);
		$all = $members->query([
			'semester' => $this->semester,
			'section' => $this->section->id
		]);

		$output('<div class="moss-names">');

		$cnt = 0;

		foreach($all as $user) {
			if($user->role !== Member::STUDENT) {
				continue;
			}

			$output('<p>' . $user->displayName);

			// Get the submission data for this user
			$data = $this->moss->get_data($this->site, $user->member);

			if(count($data) == 0) {
				$output(" <em>no submission</em></p>");
				continue;
			}

			$output("</p>");

			if($this->moss->type === 'zip') {
				$this->process_zip($output, $user, reset($data), $moss);
			} else {
				foreach($data as $filename => $filedata) {
					$moss->add_raw($filename, $filedata, $this->moss->language);
				}
			}

			$cnt++;
			if($this->moss->limit !== null && $cnt >= $this->moss->limit) {
				break;
			}
		}

		$output('</div>');

		$output("<pre>Sending to MOSS\n");
		$moss->setCommentString("MOSS");
		try {
			$moss->send($output);
			$output("</pre><p>--done--</p>");
		} catch(\Exception $ex) {
			$msg = $ex->getMessage();
			$output("</pre><p>$msg</p>");
		}
	}

	private function process_zip($output, User $user, $data, $moss) {
		$analyzer = new Analyzer($this->submission);

		$dir = $analyzer->get_temp_dir($user->userId . "_");
		$path = $dir . DIRECTORY_SEPARATOR . 'submit.zip';
		$fp = fopen($path, "wb");
		fwrite($fp, $data['binary']);
		fclose($fp);

		$analyzer->set_path($path);
		$unzipped = $analyzer->get_unzipped_dir($this->site);
		unlink($path);

		$include = $this->moss->include;
		$exclude = $this->moss->exclude;
		$strip = "#^.*/unzip/#";

		$files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($unzipped));
		foreach($files as $file => $obj){
			if(preg_match($include, $file) == 1) {
				// This is an included file
				if($exclude !== null && preg_match($exclude, $file) == 1) {
					//echo "Exclude:" . $file . "<br>";
					continue;
				}

				$filename = '/' . $user->userId. '/' . preg_replace($strip, '', $file);
				$moss->add_raw($filename, file_get_contents($file), $this->moss->language);
			}
		}

		$analyzer->close();
	}


	private $assignment;
	private $submission;
	private $moss;
}
