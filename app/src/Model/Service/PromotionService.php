<?php

namespace App\Model\Service;

use App\Entity\Partner;
use App\Entity\Prize;
use App\Entity\Promotion;
use App\Entity\User;
use App\Entity\Winning;
use App\Model\Constant\DateTimeFormats;
use App\Repository\PrizeRepository;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\LockMode;
use Doctrine\DBAL\TransactionIsolationLevel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

class PromotionService {

  private const MORNING_START_TIME = '00:00:00';
  private const MORNING_END_TIME = '09:00:00';
  private const EVENING_START_TIME = '20:00:00';
  private const EVENING_END_TIME = '23:59:59';

  private Request $request;

  // Inject needed services
  public function __construct(
    private readonly EntityManagerInterface $em,
    private readonly TranslatorInterface    $translator,
    private readonly PrizeRepository        $prizeRepository,
    RequestStack                            $request
  ) {
    $this->request = $request->getCurrentRequest();
  }

  public function validate(User $user, Promotion $promotion, DateTime $currentDate): array {
    // Initialize an empty array that will serve as error container
    $errors = [];

    // Get promotion days
    $promotionDays = $this->getPromotionDays($promotion);
    $currentDateFormatted = $currentDate->format(DateTimeFormats::DATE_FORMAT);

    // Check if current date is not outside of promotion dates
    if ($currentDateFormatted !== $promotionDays['first'] && $currentDateFormatted !== $promotionDays['second']) {
      $errors[] = $this->translator->trans('promotions.outsideDate', locale: $this->request->getLocale());
    }

    // No prizes are to be given from 00:00:00 to 09:00:00 and 20:00:00 to 23:59:59
    if ($this->isAllowedToDrawThePrize() === false) {
      $errors[] = $this->translator->trans('promotions.outsideHours', locale: $this->request->getLocale());
    }

    // The user can no longer play if he won a prize already that day
    $userPlayed = $this->em->getRepository(Winning::class)->findOneBy([
      'user' => $user,
      'promotion' => $promotion,
      'date' => $currentDate
    ]);

    if ($userPlayed) {
      $errors[] = $this->translator->trans('promotions.userPlayed', locale: $this->request->getLocale());
    }

    return $errors;
  }

  public function getRandomPrize(Promotion $promotion, DateTime $currentDate): ?Prize {
    // Get total number of prizes for this promotion
    $total = $this->prizeRepository->count(['promotion' => $promotion]);

    // Initialize ranges by default for the first day
    $offset = 0;
    $limit = ceil($total / 2);

    // Get promotion days
    $promotionDays = $this->getPromotionDays($promotion);

    // In case that promotion should be made on second day
    if ($promotionDays['second'] === $currentDate->format(DateTimeFormats::DATE_FORMAT)) {
      // Swap offset with the limit && limit with the total number
      $offset = $limit;
      $limit = $total;
    }

    // Get all prizes that are available
    $allPrizes = $this->prizeRepository->getAvailablePrizes($promotion, $limit, $offset);

    // In case there are any prizes available
    if (!empty($allPrizes)) {
      // Pick a random prize
      $rand = mt_rand(0, count($allPrizes) - 1);

      // Return it
      return $allPrizes[$rand];
    }

    // Otherwise just return null
    return null;
  }

  /**
   * Check if a promotion is eligible to be drawn on specific time ranges
   *
   * @return bool
   */
  public function isAllowedToDrawThePrize(): bool {
    // Create DateTime objects for the start and end times
    $morningStart = new DateTime(self::MORNING_START_TIME);
    $morningEnd = new DateTime(self::MORNING_END_TIME);
    $eveningStart = new DateTime(self::EVENING_START_TIME);
    $eveningEnd = new DateTime(self::EVENING_END_TIME);

    // Get the current time as a DateTime object
    $current = new DateTime();

    // Check if the current time is between the morning or evening time ranges
    return !($current >= $morningStart && $current <= $morningEnd) &&
      !($current >= $eveningStart && $current <= $eveningEnd);
  }

  /**
   * Get the promotion span days based on its start date
   *
   * @param Promotion $promotion
   * @param bool $formatted
   * @return array
   */
  private function getPromotionDays(Promotion $promotion, bool $formatted = true): array {
    // Span 2 day promotion by promotion start date
    $firstDay = $promotion->getStartDate();
    $secondDay = clone $firstDay;
    $secondDay->modify('+1 day');

    return [
      'first' => $formatted ? $firstDay->format(DateTimeFormats::DATE_FORMAT) : $firstDay,
      'second' => $formatted ? $secondDay->format(DateTimeFormats::DATE_FORMAT) : $secondDay
    ];
  }


  /**
   * Get the promotion response data
   *
   * @param Prize $prize
   * @param Partner|null $partner
   * @param string $locale
   * @return array
   */
  public function getPromotionResponse(Prize $prize, ?Partner $partner, string $locale): array {
    // Prepare the response data
    $response = [
      'id' => $prize->getPromotion()->getId(),
      'name' => $prize->getPromotion()->getName(),
      'startDate' => $prize->getPromotion()->getStartDate()->format(DateTimeFormats::FULL_FORMAT),
      'prize' => [
        'name' => $prize->translate($locale)->getName(),
        'code' => $prize->getCode(),
      ],
    ];

    // In case partner is not null add partner's data
    if ($partner) {
      $response['partner'] = [
        'name' => $partner->translate($locale)->getName(),
        'code' => $partner->getCode(),
        'url' => $partner->getUrl()
      ];
    }

    return $response;
  }
}
