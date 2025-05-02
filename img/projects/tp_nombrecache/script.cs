/**
 * Jeu du nombre caché
 * author : Emds
 * date : 06/07/2020
 */
using System;
using System.Windows.Forms;
using Serilog;
using Serilog.Formatting.Json;

namespace NombreCache
{
    public partial class FrmNombreCache : Form
    {
        // déclaration globale
        private int phase;  // phase 1 : saisie du nombre à chercher ; pahase 2 : recherche
        private int valeurAChercher; // contiendra la valeur à chercher
        private int nbEssai; // nombre d'essais pour trouver la valeur

        /// <summary>
        /// initialisation des composants graphiques
        /// </summary>
        public FrmNombreCache()
        {
            InitializeComponent();
        }

        /// <summary>
        /// Chargement de la fenêtre : initialisations pour commencer le jeu
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private void frmNombreCache_Load(object sender, EventArgs e)
        {
            Log.Logger = new LoggerConfiguration()
                .MinimumLevel.Verbose()
                .WriteTo.Console()
                .WriteTo.File(new JsonFormatter(), "logs/log.txt", rollingInterval: RollingInterval.Day)
                .WriteTo.File("logs/errorLog.txt", restrictedToMinimumLevel: Serilog.Events.LogEventLevel.Information)
                .WriteTo.EventLog("NombreCache", manageEventSource: true, restrictedToMinimumLevel: Serilog.Events.LogEventLevel.Fatal)
                .CreateLogger();
            btnRejouer_Click(null, null);
        }

        /// <summary>
        /// Réinitialisations au début du jeu ou au début des tentatives
        /// </summary>
        private void initialiser()
        {
            if (phase == 1)
            {
                grpValeur.Text = "Valeur (entre 1 et 100)";
                grpReponse.Visible = false;
            }
            else
            {
                Log.Debug("Méthode initialiser, else du test sur phase qui doit être à 2. phase = " + phase);
                grpValeur.Text = "Essai (entre 1 et 100)";
                nbEssai = 0;
                grpReponse.Text = "";
                grpReponse.Visible = true;
            }
            lblMessage.Text = "";
            efface();
        }

        /// <summary>
        /// Clic sur le bouton btnRejouer (flèche ronde) : démarrage d'un nouveau jeu
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private void btnRejouer_Click(object sender, EventArgs e)
        {
            phase = 1;
            initialiser();
        }

        /// <summary>
        /// Clic sur le bouton btnQuitter : fermeture de l'application
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private void btnQuitter_Click(object sender, EventArgs e)
        {
            Application.Exit();
        }

        /// <summary>
        /// Clic sur le bouton btnValider (OK)
        /// Si 1ère phase de jeu : contrôle le nombre(entre 1 et 100) et lance la 2ème phase(recherche)
        /// Si 2ème phase de jeu : compare l'essai avec le nombre de départ et affiche le message
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private void btnValider_Click(object sender, EventArgs e)
        {
            int valeur = 0;
            //  contrôle si la valeur saisie est bien un entier
            try
            {
                valeur = int.Parse(txtValeur.Text);
                // contrôle si le nombre est entre 1 et 100
                if (valeur<1 || valeur > 100)
                {
                    efface();
                }
                else
                {
                    if (phase == 1)
                    {
                        // mémorisation de la valeur à chercher
                        valeurAChercher = valeur;
                        // passage à la phase 2
                        phase = 2;
                        initialiser();
                    }
                    else
                    {
                        // affiche le nombre d'essais
                        nbEssai++;
                        grpReponse.Text = "Essai n°" + nbEssai;
                        // comparaison et affichage du message correspondant
                        if (valeur == valeurAChercher)
                        {
                            lblMessage.Text = "Bravo !!! C'était bien "+valeurAChercher;
                            efface();
                            btnRejouer.Focus();
                        }
                        else
                        {
                            if (valeur < valeurAChercher)
                            {
                                lblMessage.Text = valeur+" est trop petit";
                            }
                            else
                            {
                                lblMessage.Text = valeur+" est trop grand";
                            }
                            efface();
                        }
                    }
                }
            }
            catch(Exception ex)
            {
                Log.Information("Erreur conversion en int de la valeur saisie. valeur = " + txtValeur.Text);
                Log.Fatal(ex, "Erreur de conversion avec message précis");
                efface();
            }
        }

        /// <summary>
        /// Efface la zone de saisie et repositionne le curseur
        /// </summary>
        private void efface()
        {
            txtValeur.Text = "";
            txtValeur.Focus();
        }

        /// <summary>
        /// Validation dans txtValeur
        /// Même effet que le clic sur le bouton ok
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private void txtValeur_KeyPress(object sender, KeyPressEventArgs e)
        {
            if (e.KeyChar == (char)Keys.Return)
            {
                btnValider_Click(null, null);
            }
        }
    }
}