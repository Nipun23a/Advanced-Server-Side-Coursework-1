<?php

namespace App\Controllers;

use App\Models\AlumniProfileModel;
use App\Models\CertificateModel;
use App\Models\DegreeModel;
use App\Models\EmploymentHistoryModel;
use App\Models\LicenseModel;
use App\Models\ProfessionalCourseModel;
use CodeIgniter\Model;

class ProfileController extends BaseController
{
    protected AlumniProfileModel $profileModel;
    protected DegreeModel $degreeModel;
    protected CertificateModel $certificateModel;
    protected LicenseModel $licenseModel;
    protected ProfessionalCourseModel $courseModel;
    protected EmploymentHistoryModel $employmentModel;

    public function __construct()
    {
        $this->profileModel = new AlumniProfileModel();
        $this->degreeModel = new DegreeModel();
        $this->certificateModel = new CertificateModel();
        $this->licenseModel = new LicenseModel();
        $this->courseModel = new ProfessionalCourseModel();
        $this->employmentModel = new EmploymentHistoryModel();
    }

    public function index()
    {
        $userId = (int) session()->get('user_id');
        $profile = $this->profileModel->findByUserId($userId);
        $profileId = $profile['id'] ?? null;

        return view('profile/index', [
            'title' => 'My Profile',
            'profile' => $profile,
            'degrees' => $profileId ? $this->degreeModel->where('profile_id', $profileId)->orderBy('completion_date', 'DESC')->findAll() : [],
            'certificates' => $profileId ? $this->certificateModel->where('profile_id', $profileId)->orderBy('completion_date', 'DESC')->findAll() : [],
            'licenses' => $profileId ? $this->licenseModel->where('profile_id', $profileId)->orderBy('completion_date', 'DESC')->findAll() : [],
            'courses' => $profileId ? $this->courseModel->where('profile_id', $profileId)->orderBy('completion_date', 'DESC')->findAll() : [],
            'employmentHistory' => $profileId ? $this->employmentModel->where('profile_id', $profileId)->orderBy('start_date', 'DESC')->findAll() : [],
            'validation' => \Config\Services::validation(),
        ]);
    }

    public function save()
    {
        $userId = (int) session()->get('user_id');

        $rules = [
            'bio' => 'permit_empty|max_length[5000]',
            'linkedin_url' => 'permit_empty|valid_url_strict',
            'profile_image_url' => 'permit_empty|valid_url_strict|max_length[256]',
            'profile_image' => 'permit_empty|is_image[profile_image]|max_size[profile_image,2048]|ext_in[profile_image,jpg,jpeg,png,gif,webp]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $data = [
            'bio' => trim((string) $this->request->getPost('bio')),
            'linkedin_url' => trim((string) $this->request->getPost('linkedin_url')),
            'profile_image_url' => trim((string) $this->request->getPost('profile_image_url')),
        ];

        $uploadedImage = $this->request->getFile('profile_image');
        if ($uploadedImage && $uploadedImage->isValid() && ! $uploadedImage->hasMoved()) {
            $uploadDirectory = FCPATH . 'uploads/profiles';
            if (! is_dir($uploadDirectory)) {
                mkdir($uploadDirectory, 0777, true);
            }

            $fileName = $uploadedImage->getRandomName();
            $uploadedImage->move($uploadDirectory, $fileName);
            $data['profile_image_url'] = '/uploads/profiles/' . $fileName;
        }

        $existingProfile = $this->profileModel->findByUserId($userId);

        if ($existingProfile) {
            $this->profileModel->update($existingProfile['id'], $data);
            return redirect()->to('/profile')->with('success', 'Profile updated successfully.');
        }

        $this->profileModel->insert([
            'user_id' => $userId,
            ...$data,
        ]);

        return redirect()->to('/profile')->with('success', 'Profile created successfully.');
    }

    public function saveDegree()
    {
        return $this->saveProfileSection(
            $this->degreeModel,
            [
                'degree_name' => 'required|max_length[100]',
                'institution_url' => 'required|valid_url_strict|max_length[256]',
                'completion_date' => 'required|valid_date[Y-m-d]',
            ],
            ['degree_name', 'institution_url', 'completion_date'],
            'Degree saved successfully.'
        );
    }

    public function deleteDegree(int $id)
    {
        return $this->deleteProfileSection($this->degreeModel, $id, 'Degree deleted successfully.');
    }

    public function saveCertificate()
    {
        return $this->saveProfileSection(
            $this->certificateModel,
            [
                'certificate_name' => 'required|max_length[100]',
                'issuer_name' => 'required|max_length[256]',
                'completion_date' => 'required|valid_date[Y-m-d]',
            ],
            ['certificate_name', 'issuer_name', 'completion_date'],
            'Certificate saved successfully.'
        );
    }

    public function deleteCertificate(int $id)
    {
        return $this->deleteProfileSection($this->certificateModel, $id, 'Certificate deleted successfully.');
    }

    public function saveLicense()
    {
        return $this->saveProfileSection(
            $this->licenseModel,
            [
                'license_name' => 'required|max_length[100]',
                'license_url' => 'required|valid_url_strict|max_length[256]',
                'completion_date' => 'required|valid_date[Y-m-d]',
                'expiration_date' => 'permit_empty|valid_date[Y-m-d]',
            ],
            ['license_name', 'license_url', 'completion_date', 'expiration_date'],
            'License saved successfully.'
        );
    }

    public function deleteLicense(int $id)
    {
        return $this->deleteProfileSection($this->licenseModel, $id, 'License deleted successfully.');
    }

    public function saveCourse()
    {
        return $this->saveProfileSection(
            $this->courseModel,
            [
                'course_name' => 'required|max_length[100]',
                'provider_url' => 'required|valid_url_strict|max_length[256]',
                'completion_date' => 'required|valid_date[Y-m-d]',
            ],
            ['course_name', 'provider_url', 'completion_date'],
            'Professional course saved successfully.'
        );
    }

    public function deleteCourse(int $id)
    {
        return $this->deleteProfileSection($this->courseModel, $id, 'Professional course deleted successfully.');
    }

    public function saveEmployment()
    {
        return $this->saveProfileSection(
            $this->employmentModel,
            [
                'company_name' => 'required|max_length[100]',
                'job_title' => 'required|max_length[100]',
                'start_date' => 'required|valid_date[Y-m-d]',
                'end_date' => 'permit_empty|valid_date[Y-m-d]',
            ],
            ['company_name', 'job_title', 'start_date', 'end_date'],
            'Employment history saved successfully.'
        );
    }

    public function deleteEmployment(int $id)
    {
        return $this->deleteProfileSection($this->employmentModel, $id, 'Employment history deleted successfully.');
    }

    protected function getProfileOrRedirect(int $userId): ?array
    {
        $profile = $this->profileModel->findByUserId($userId);
        if ($profile) {
            return $profile;
        }

        session()->setFlashdata('error', 'Create your main profile first.');
        return null;
    }

    protected function saveProfileSection(Model $model, array $rules, array $fields, string $successMessage)
    {
        $userId = (int) session()->get('user_id');
        $profile = $this->getProfileOrRedirect($userId);
        if (! $profile) {
            return redirect()->to('/profile')->withInput();
        }

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $recordId = (int) $this->request->getPost('id');
        $data = ['profile_id' => $profile['id']];

        foreach ($fields as $field) {
            $value = $this->request->getPost($field);
            $data[$field] = is_string($value) ? trim($value) : $value;
        }

        if ($recordId > 0) {
            $record = $model->find($recordId);
            if (! $record || (int) $record['profile_id'] !== (int) $profile['id']) {
                return redirect()->to('/profile')->with('error', 'Record not found.');
            }
            $model->update($recordId, $data);
        } else {
            $model->insert($data);
        }

        return redirect()->to('/profile')->with('success', $successMessage);
    }

    protected function deleteProfileSection(Model $model, int $id, string $successMessage)
    {
        $userId = (int) session()->get('user_id');
        $profile = $this->getProfileOrRedirect($userId);
        if (! $profile) {
            return redirect()->to('/profile');
        }

        $record = $model->find($id);
        if (! $record || (int) $record['profile_id'] !== (int) $profile['id']) {
            return redirect()->to('/profile')->with('error', 'Record not found.');
        }

        $model->delete($id);
        return redirect()->to('/profile')->with('success', $successMessage);
    }
}
